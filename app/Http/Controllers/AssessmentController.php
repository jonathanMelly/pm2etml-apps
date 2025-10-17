<?php

namespace App\Http\Controllers;

use App\Constants\AssessmentState;
use App\Constants\AssessmentTiming;
use App\Constants\RoleName;
use App\Services\AssessmentStateMachine;
use App\Http\Requests\StoreEvaluationRequest;
use App\Models\AssessmentCriterionTemplate;
use App\Models\WorkerContract;
use App\Models\WorkerContractAssessment;
use Illuminate\Support\Facades\Log;

class AssessmentController extends Controller
{
    private array $visibleCursors;

    public function __construct()
    {
        $this->visibleCursors = [
            AssessmentTiming::AUTO_FORMATIVE => true,
            AssessmentTiming::EVAL_FORMATIVE => true,
            AssessmentTiming::AUTO_FINALE    => false,
            AssessmentTiming::EVAL_FINALE    => false,
        ];
    }

    /**
     * Enregistre une évaluation (formative ou finale).
     */
    public function storeEvaluation(StoreEvaluationRequest $request)
    {
        try {
            $raw = $request->input('evaluation_data');

            // Décodage sûr (car souvent string JSON)
            $data = is_array($raw) ? $raw : json_decode($raw ?? '', true);

            Log::info('Requête reçue', ['full_request' => $request->all()]);
            Log::info('Decoded evaluation_data', $data ?? ['_decoded' => 'null']);

            if (empty($data) || !is_array($data)) {
                return response()->json(['success' => false, 'message' => 'Aucune donnée reçue.'], 422);
            }

            $response = $this->createOrUpdateEvaluation($data);

            Log::info('storeEvaluation process finish evaluation_data', ['data' => $data]);

            if ($request->wantsJson()) {
                return $response;
            }

            $responseData = $response->getData(true);
            $ids = $request->input('ids');

            if (($responseData['success'] ?? false) && $ids) {
                return redirect()->to("/evaluation/fullEvaluation/{$ids}")
                    ->with('success', $responseData['message']);
            }

            return redirect()->back()->with('error', 'Erreur lors de la sauvegarde.')->withInput();
        } catch (\Throwable $e) {
            Log::error('Erreur storeEvaluation', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }


    /**
     * Création ou mise à jour d'une évaluation complète.
     */
    private function createOrUpdateEvaluation(array $data)
    {
        $user = auth()->user();
        $workerContractId = $data['worker_contract_id'] ?? null;

        if (!$workerContractId) {
            throw new \InvalidArgumentException('ID du contrat manquant.');
        }

        // Charger le WorkerContract pour obtenir les infos liées
        $workerContract = \App\Models\WorkerContract::with([
            'groupMember.group.groupName',
            'contract.jobDefinition',
            'groupMember.user',
            'contract.clients',
        ])->find($workerContractId);

        if (!$workerContract) {
            Log::error("[createOrUpdateEvaluation] Contrat introuvable", ['worker_contract_id' => $workerContractId]);
            throw new \RuntimeException("WorkerContract introuvable (ID $workerContractId)");
        }

        // Déterminer les IDs nécessaires
        $teacherId = $data['evaluator_id'] ?? $user->id;
        $studentId = $data['student_id'] ?? $workerContract->groupMember?->user_id;
        $jobId     = $data['job_id'] ?? $workerContract->contract?->job_definition_id;
        $classId   = $data['student_class_id'] ?? $workerContract->groupMember?->group?->group_name_id;
        $jobTitle  = $data['job_title'] ?? $workerContract->contract?->jobDefinition?->title ?? 'Sans titre';
        $status    = $data['status_eval'] ?? 'not_started';
        $isUpdate  = filter_var($data['isUpdate'] ?? false, FILTER_VALIDATE_BOOLEAN);

        //  Vérification minimale
        if (!$teacherId || !$studentId || !$jobId || !$classId) {
            Log::warning('[createOrUpdateEvaluation] Données manquantes', [
                'teacher_id' => $teacherId,
                'student_id' => $studentId,
                'job_id'     => $jobId,
                'class_id'   => $classId,
            ]);
        }

        // Extraire les appréciations
        $appreciations = $data['evaluations']['appreciations'] ?? [];
        $studentRemark = $data['student_remark'] ?? '';

        //  Structure de données commune
        $evaluationPayload = [
            'worker_contract_id' => $workerContractId,
            'teacher_id'         => $teacherId,
            'student_id'         => $studentId,
            'job_id'             => $jobId,
            'class_id'           => $classId,
            'job_title'          => $jobTitle,
            'status'             => $status,
            'date'               => now(),
            'student_remark'     => $studentRemark,
            'appreciations'      => $data['evaluations']['appreciations'] ?? [],
        ];

        //  Création ou mise à jour selon isUpdate
        $evaluation = null;
        if ($isUpdate) {
            $evaluation = \App\Models\WorkerContractAssessment::where('worker_contract_id', $workerContractId)->first();

            if ($evaluation) {
                $evaluation->updateWithAssessments($evaluationPayload);
                Log::info('[createOrUpdateEvaluation] Évaluation mise à jour', ['id' => $evaluation->id]);
            } else {
                Log::warning('[createOrUpdateEvaluation] Aucun enregistrement trouvé à mettre à jour', [
                    'worker_contract_id' => $workerContractId
                ]);
                $evaluation = \App\Models\WorkerContractAssessment::createWithAssessments($evaluationPayload);
            }
        } else {
            $evaluation = \App\Models\WorkerContractAssessment::createWithAssessments($evaluationPayload);
            Log::info('[createOrUpdateEvaluation] Nouvelle évaluation créée', ['id' => $evaluation->id]);
        }

        //  Déterminer et persister le statut workflow courant (obligatoire) à partir des timings (précis)
        $assessmentsArray = $evaluation->assessments()->get()->toArray();
        $timings = collect($assessmentsArray)->pluck('timing')->toArray();
        $evaluation->status = $this->computePreciseWorkflowStatus($timings, $evaluation->status ?? null);
        $evaluation->save();

        return response()->json([
            'success' => true,
            'message' => $isUpdate
                ? 'Évaluation mise à jour avec succès.'
                : 'Évaluation créée avec succès.',
            'data' => $evaluation->load('assessments.criteria'),
        ]);
    }

    /**
     * Détermine le statut actuel d'une évaluation selon les timings remplis.
     * Priorité : EVAL_FINALE > EVAL_FORMATIVE > AUTO_FINALE > AUTO_FORMATIVE > not_started
     */
    private function getCurrentStatus(array $assessments): string
    {
        $timings = collect($assessments)->pluck('timing')->map(fn($t) => $this->normalizeTiming($t))->toArray();

        if (in_array(AssessmentTiming::EVAL_FINALE, $timings, true))     return AssessmentTiming::EVAL_FINALE;
        if (in_array(AssessmentTiming::EVAL_FORMATIVE, $timings, true))  return AssessmentTiming::EVAL_FORMATIVE;
        if (in_array(AssessmentTiming::AUTO_FINALE, $timings, true))     return AssessmentTiming::AUTO_FINALE;
        if (in_array(AssessmentTiming::AUTO_FORMATIVE, $timings, true))  return AssessmentTiming::AUTO_FORMATIVE;

        return 'not_started';
    }

    /**
     * Normalise les libellés venant du front (auto_formative / autoFormative, etc.)
     * vers les constantes d’AssessmentTiming.
     */
    private function normalizeTiming(?string $raw): string
    {
        if (!$raw) return 'not_started';

        $k = str_replace(['-', ' '], '_', strtolower($raw));

        return match ($k) {
            // anciens identifiants
            'auto_formative',  'autoformative'  => AssessmentTiming::AUTO_FORMATIVE,
            'eval_formative',  'evalformative'  => AssessmentTiming::EVAL_FORMATIVE,
            'auto_finale',     'autofinale'     => AssessmentTiming::AUTO_FINALE,
            'eval_finale',     'evalfinale'     => AssessmentTiming::EVAL_FINALE,
            // nouveaux identifiants
            'a_formative1', 'aformative1'       => AssessmentTiming::AUTO_FORMATIVE,
            'e_formative1', 'eformative1'       => AssessmentTiming::EVAL_FORMATIVE,
            'a_formative2', 'aformative2'       => AssessmentTiming::AUTO_FINALE,
            'e_sommative',  'esommative'        => AssessmentTiming::EVAL_FINALE,
            default => in_array($raw, AssessmentTiming::all(), true) ? $raw : 'not_started',
        };
    }

    /**
     * Charge les évaluations d'un contrat.
     */
    public function loadEvaluationsByContract(int $contractId)
    {
        $evaluations = WorkerContractAssessment::with(['assessments.criteria'])
            ->where('worker_contract_id', $contractId)
            ->get();

        if ($evaluations->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Aucune évaluation trouvée.'], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Évaluations chargées avec succès.',
            'data' => $evaluations,
        ]);
    }

    /**
     * Récupère les critères par catégorie (global ou personnalisés).
     */
    private function getCriteriaGrouped($userId)
    {
        $hasCustom = AssessmentCriterionTemplate::where('user_id', $userId)->exists();

        $query = AssessmentCriterionTemplate::with('category');
        if ($hasCustom) {
            $query->where('user_id', $userId);
        } else {
            $query->whereNull('user_id')->orWhere('user_id', 0);
        }

        $criteria = $query->orderBy('position')->get();
        return $criteria->groupBy(fn($c) => $c->category->name ?? 'Autres');
    }

    public function getCriterias()
    {
        return AssessmentCriterionTemplate::where('user_id', 0)->get();
    }

    private function mapEvaluationForDisplay(WorkerContractAssessment $evaluation): array
    {
        $assessments = $evaluation->assessments()->with('criteria.template')->get();

        return $assessments->map(function ($assessment) {
            return [
                'level' => $this->normalizeTiming($assessment->timing),
                'date' => $assessment->date ? $assessment->date->toDateTimeString() : null,
                'student_remark' => $assessment->student_remark ?? '',
                'criteria' => $assessment->criteria->map(function ($criterion) {
                    return [
                        'id' => $criterion->template_id,
                        'name' => $criterion->template->name ?? 'Critère inconnu',
                        'value' => $criterion->value,
                        'checked' => (bool) $criterion->checked,
                        'remark' => $criterion->remark_criteria,
                    ];
                })->values(),
            ];
        })->toArray();
    }

    /**
     * Définit les catégories visibles par défaut (toutes à true).
     */
    private function getInitialVisibleCategories($criteriaGrouped)
    {
        $visible = [];
        foreach ($criteriaGrouped as $key => $value) {
            $visible[$key] = true;
        }
        return $visible;
    }

    /**
     * Calcule un statut workflow le plus précis possible à partir des timings présents
     * et du statut courant éventuellement enregistré.
     */
    private function computePreciseWorkflowStatus(array $rawTimings, ?string $currentStatus): string
    {
        // Normaliser tous les timings vers les constantes d'AssessmentTiming
        $normTimings = collect($rawTimings)
            ->map(fn($t) => $this->normalizeTiming($t))
            ->filter()
            ->values()
            ->all();

        $has = fn(string $v) => in_array($v, $normTimings, true);

        // ENS-S présent
        if ($has(\App\Constants\AssessmentTiming::EVAL_FINALE)) {
            // Si déjà terminé
            if ($currentStatus === \App\Constants\AssessmentState::COMPLETED->value
                || $currentStatus === \App\Constants\AssessmentWorkflowState::CLOSED_BY_TEACHER->value) {
                return \App\Constants\AssessmentWorkflowState::CLOSED_BY_TEACHER->value;
            }
            return \App\Constants\AssessmentWorkflowState::TEACHER_SUMMATIVE_DONE->value;
        }

        // ELEV-F2 présent, pas ENS-S → attente de validation F2 par enseignant
        if ($has(\App\Constants\AssessmentTiming::AUTO_FINALE)) {
            if ($currentStatus === \App\Constants\AssessmentWorkflowState::TEACHER_ACK_FORMATIVE2->value) {
                // Après validation F2 par enseignant, on peut proposer ENS‑S
                return \App\Constants\AssessmentWorkflowState::WAITING_TEACHER_SUMMATIVE->value;
            }
            return \App\Constants\AssessmentWorkflowState::WAITING_TEACHER_VALIDATION_F2->value;
        }

        // ENS-F présent, pas F2
        if ($has(\App\Constants\AssessmentTiming::EVAL_FORMATIVE)) {
            // Si un statut plus précis est déjà présent, le conserver
            if (in_array($currentStatus, [
                \App\Constants\AssessmentWorkflowState::WAITING_STUDENT_VALIDATION_F->value,
                \App\Constants\AssessmentWorkflowState::FORMATIVE_VALIDATED->value,
                \App\Constants\AssessmentWorkflowState::WAITING_STUDENT_FORMATIVE2_OPT->value,
                \App\Constants\AssessmentWorkflowState::TEACHER_FORMATIVE_DONE->value,
            ], true)) {
                return $currentStatus;
            }
            // Par défaut, enregistrer que l'enseignant a terminé la formative
            return \App\Constants\AssessmentWorkflowState::TEACHER_FORMATIVE_DONE->value;
        }

        // ELEV-F1 présent, pas ENS-F
        if ($has(\App\Constants\AssessmentTiming::AUTO_FORMATIVE)) {
            // Si l'enseignant a déjà accusé réception, conserver
            if ($currentStatus === \App\Constants\AssessmentWorkflowState::TEACHER_ACK_FORMATIVE->value) {
                return $currentStatus;
            }
            return \App\Constants\AssessmentWorkflowState::WAITING_TEACHER_VALIDATION_F->value;
        }

        // Par défaut
        return \App\Constants\AssessmentWorkflowState::WAITING_STUDENT_FORMATIVE->value;
    }

    /**
     * API: Met à jour l'état d'une évaluation en appliquant la prochaine transition autorisée
     * selon le rôle (enseignant/élève). Retourne le nouvel état.
     */
    public function updateStatus(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'evaluation_id' => 'required|integer',
            'new_state'     => 'nullable|string', // éventuellement fourni mais pas obligatoire
        ]);

        $evaluation = \App\Models\WorkerContractAssessment::with('assessments')->find($request->integer('evaluation_id'));
        if (!$evaluation) {
            return response()->json(['success' => false, 'message' => 'Évaluation introuvable.'], 404);
        }

        $user = auth()->user();
        $role = $user->hasRole(RoleName::TEACHER) ? 'teacher' : ($user->hasRole(RoleName::STUDENT) ? 'student' : 'other');

        // État courant basé sur les assessments existants
        $machine = new AssessmentStateMachine($evaluation->assessments->toArray());
        $current = $machine->getCurrentState();

        // Utiliser le workflow pour piloter un statut informatif (sans créer d'assessment)
        $currentWorkflow = (new AssessmentStateMachine($evaluation->assessments->toArray()))
            ->getWorkflowState($evaluation->status ?? null);

        $newWorkflow = null;

        if ($role === 'teacher') {
            // L'enseignant accuse réception de l'auto-évaluation formative de l'élève
            if ($currentWorkflow->value === \App\Constants\AssessmentWorkflowState::WAITING_TEACHER_VALIDATION_F->value) {
                $newWorkflow = \App\Constants\AssessmentWorkflowState::TEACHER_ACK_FORMATIVE;
            }
            // Validation de la formative 2 (ELEV-F2)
            if ($currentWorkflow->value === \App\Constants\AssessmentWorkflowState::WAITING_TEACHER_VALIDATION_F2->value) {
                $newWorkflow = \App\Constants\AssessmentWorkflowState::TEACHER_ACK_FORMATIVE2;
            }
            // Après avoir fait son éval sommative (ENS‑S), l'enseignant peut terminer directement
            if ($currentWorkflow->value === \App\Constants\AssessmentWorkflowState::TEACHER_SUMMATIVE_DONE->value) {
                // Marquer terminé côté DB avec l'état final 'completed'
                $evaluation->status = \App\Constants\AssessmentState::COMPLETED->value;
                $evaluation->save();
                return response()->json([
                    'success' => true,
                    'message' => 'Évaluation terminée.',
                    'previous_state' => $current->value,
                    'new_state' => $evaluation->status,
                    'workflow' => \App\Constants\AssessmentWorkflowState::CLOSED_BY_TEACHER->value,
                    'ack' => true,
                ]);
            }
        } elseif ($role === 'student') {
            // L'élève valide la sommative de l'enseignant (accusé de réception)
            if ($currentWorkflow->value === \App\Constants\AssessmentWorkflowState::TEACHER_SUMMATIVE_DONE->value) {
                $evaluation->status = \App\Constants\AssessmentWorkflowState::SUMMATIVE_VALIDATED->value;
                $evaluation->save();

                return response()->json([
                    'success' => true,
                    'message' => 'Validation élève enregistrée.',
                    'previous_state' => $current->value,
                    'new_state' => $evaluation->status,
                    'workflow' => \App\Constants\AssessmentWorkflowState::SUMMATIVE_VALIDATED->value,
                    'ack' => true,
                ]);
            }
        }

        if ($newWorkflow) {
            $evaluation->status = $newWorkflow->value;
            $evaluation->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Validation enregistrée.',
            'previous_state' => $current->value,
            'new_state' => $evaluation->status ?? $current->value,
            'workflow' => $newWorkflow?->value ?? $currentWorkflow->value,
            'ack' => true,
        ]);
    }

    /**
     * API: Transition explicite (optionnel). Permet de forcer une étape spécifique
     * ou de marquer terminé/signature. Garde une logique sûre par rapport au rôle.
     */
    public function handleTransition(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'evaluationId' => 'required|integer',
            'status'       => 'required|string',
            'isTeacher'    => 'nullable|boolean',
        ]);

        $evaluation = \App\Models\WorkerContractAssessment::with('assessments')->find($request->integer('evaluationId'));
        if (!$evaluation) {
            return response()->json(['success' => false, 'message' => 'Évaluation introuvable.'], 404);
        }

        $status = $this->normalizeTiming($request->string('status'));
        $user = auth()->user();
        $role = $user->hasRole(RoleName::TEACHER) ? 'teacher' : ($user->hasRole(RoleName::STUDENT) ? 'student' : 'other');

        // Mappe vers l'enum d'état si possible, sinon timing camelCase
        $targetState = \App\Constants\AssessmentState::tryFrom($status) ?? null;
        $timing = in_array($status, AssessmentTiming::all(), true) ? $status : null;

        if ($timing) {
            // Création d'une étape d'évaluation (remarque stockée au niveau assessment)
            $evaluation->addAssessmentWithCriteria($timing, [], null);
        } elseif ($targetState) {
            // États hors timing
            $evaluation->status = $targetState->value;
            $evaluation->save();
        } else {
            return response()->json(['success' => false, 'message' => 'Statut ou timing invalide.'], 422);
        }

        // Recalcule et persiste le statut global actuel
        $evaluation->status = $this->getCurrentStatus($evaluation->assessments()->get()->toArray());
        $evaluation->save();

        return response()->json([
            'success' => true,
            'message' => 'Transition enregistrée.',
            'new_state' => $evaluation->status,
            'role' => $role,
        ]);
    }

    /**
     * Convertit un AssessmentState (enum) en timing (string) si applicable.
     */
    private function stateToTiming(\App\Constants\AssessmentState $state): ?string
    {
        return match ($state) {
            \App\Constants\AssessmentState::AUTO_FORMATIVE   => AssessmentTiming::AUTO_FORMATIVE,
            \App\Constants\AssessmentState::EVAL_FORMATIVE   => AssessmentTiming::EVAL_FORMATIVE,
            \App\Constants\AssessmentState::AUTO_FINALE      => AssessmentTiming::AUTO_FINALE,
            \App\Constants\AssessmentState::EVAL_FINALE      => AssessmentTiming::EVAL_FINALE,
            default => null,
        };
    }

    public function fullEvaluation(string $ids)
    {
        try {
            $user = auth()->user();
            $isTeacher = $user->hasRole(RoleName::TEACHER);
            $idsArray = explode(',', $ids);

            Log::info('[fullEvaluation] Démarrage de la récupération des évaluations', [
                'user_id' => $user->id,
                'role' => $isTeacher ? 'teacher' : 'student',
                'ids_reçus' => $idsArray,
            ]);

            $workerContracts = WorkerContract::with([
                'groupMember.user',                 // élève
                'groupMember.group.groupName',      // classe (nom)
                'contract.jobDefinition',           // job
                'contract.clients',                 // enseignants (clients du contrat)
                'workerContractAssessment.teacher', // enseignant de l’évaluation (si créé)
                'workerContractAssessment.student', // élève de l’évaluation
                'workerContractAssessment.assessments.criteria.template',
            ])->whereIn('id', $idsArray)->get();

            if ($workerContracts->isEmpty()) {
                Log::warning('Aucun WorkerContract trouvé pour les IDs transmis', ['ids' => $idsArray]);
                abort(404, 'Aucun contrat trouvé.');
            }

            Log::info('Contrats récupérés', [
                'count' => $workerContracts->count(),
                'ids_trouvés' => $workerContracts->pluck('id')->toArray(),
            ]);

            $studentsDetails = $workerContracts->map(function ($wc) {
                $evaluation = $wc->workerContractAssessment;
                $student = $evaluation?->student ?? $wc->groupMember?->user;

                $teacher = $evaluation?->teacher
                    ?? $wc->contract?->clients?->first();

                // ➜ Calcul de l’état courant via la machine d’état
                $assessments = $evaluation?->assessments ?? collect();
                //  Si des appréciations existent, on calcule l’état courant avec la machine d’état
                $state = $evaluation && $evaluation->assessments->isNotEmpty()
                    ? (new AssessmentStateMachine($evaluation->assessments->toArray()))->getCurrentState()
                    : AssessmentState::NOT_EVALUATED;

                $statusEval = $state->value; // ex: 'autoFormative', 'evalFinale', 'not_started', ...
                $workflowState = (new AssessmentStateMachine($assessments->toArray()))->getWorkflowState($evaluation?->status ?? null)->value;

                Log::debug('[WorkerContract] Détails de traitement', [
                    'worker_contract_id' => $wc->id,
                    'student' => $student ? "{$student->firstname} {$student->lastname}" : '❌ Aucun élève trouvé',
                    'teacher' => $teacher ? "{$teacher->firstname} {$teacher->lastname}" : '❌ Aucun enseignant trouvé',
                    'source_teacher' => $evaluation?->teacher ? 'worker_contract_assessment' : 'contract_client',
                    'evaluation_id' => $evaluation?->id ?? '—',
                    'computed_status' => $statusEval,
                ]);

                return (object) [
                    'worker_contract_id'  => $wc->id,
                    'student_id'          => $student?->id,
                    'student_firstname'   => $student?->firstname ?? '',
                    'student_lastname'    => $student?->lastname ?? '',
                    'class_name'          => $wc->groupMember?->group?->groupName?->name ?? '—',
                    'class_id'            => $wc->groupMember?->group?->group_name_id ?? null,
                    'job_title'           => $wc->contract?->jobDefinition?->title ?? '—',
                    'job_id'              => $wc->contract?->job_definition_id ?? null,
                    'evaluator_firstname' => $teacher?->firstname ?? '',
                    'evaluator_lastname'  => $teacher?->lastname ?? '',
                    'evaluator_id'        => $teacher?->id ?? null,
                    'evaluation_id'       => $evaluation?->id ?? null,
                    'status_eval'         => $statusEval, // ← état principal (timing prioritaire)
                    'workflow_state'      => $workflowState, // ← état enrichi (aides / transitions)
                    'evaluation'          => $evaluation ? $this->mapEvaluationForDisplay($evaluation) : [],
                ];
            });

            $missingTeacherCount = $studentsDetails->whereNull('evaluator_id')->count();
            if ($missingTeacherCount > 0) {
                Log::warning("$missingTeacherCount évaluations sans enseignant détectées.", [
                    'students_concernés' => $studentsDetails
                        ->whereNull('evaluator_id')
                        ->pluck('student_lastname', 'student_id')
                        ->toArray(),
                ]);
            }

            $criteriaGrouped = $this->getCriteriaGrouped($isTeacher ? $user->id : 0);
            $groupKeys = match (true) {
                $criteriaGrouped instanceof \Illuminate\Support\Collection => $criteriaGrouped->keys()->toArray(),
                is_array($criteriaGrouped) => array_keys($criteriaGrouped),
                default => ['type_inconnu' => gettype($criteriaGrouped)],
            };

            Log::info('Critères d’évaluation chargés', ['groupes' => $groupKeys]);

            // ➜ Alimentation du JSON côté front : inclure status_eval
            $jsonSave = $studentsDetails->map(fn($student) => [
                'student_id'         => $student->student_id,
                'student_lastname'   => $student->student_lastname,
                'student_firstname'  => $student->student_firstname,
                'worker_contract_id' => $student->worker_contract_id,
                'evaluations'        => $student->evaluation,
                'status_eval'        => $student->status_eval,
                'workflow_state'     => $student->workflow_state,
                'evaluation_id'      => $student->evaluation_id,
                'next_state'         => $student->next_state ?? null, // ← optionnel
            ])->toArray();


            Log::info('Données prêtes pour le front', [
                'students' => count($jsonSave),
                'example' => $jsonSave[0] ?? '—'
            ]);

            return view('contracts-fullEvaluation', [
                'studentsDatas'      => $studentsDetails,
                'criteriaGrouped'    => $criteriaGrouped,
                'visibleCategories'  => $this->getInitialVisibleCategories($criteriaGrouped),
                'visibleSliders'     => $this->visibleCursors,
                'isTeacher'          => $isTeacher,
                'jsonSave'           => $jsonSave,
            ]);
        } catch (\Throwable $e) {
            Log::error('Erreur dans fullEvaluation()', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'trace' => $e->getTraceAsString(),
            ]);
            abort(500, 'Erreur lors du chargement des évaluations.');
        }
    }
}
