<?php

namespace App\Http\Controllers;

// Namespaces Laravel
use App\Constants\RoleName;
use App\Http\Requests\StoreEvaluationRequest;
use App\Models\Contract;
use App\Models\Assessment;
use App\Models\AssessmentCriterion;
use App\Models\AssessmentCriterionTemplate;
use App\Models\WorkerContractAssessment;
use App\Services\AssessmentStateMachine;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


// Modèles

// Requêtes personnalisées

// Constantes ou enums


class AssessmentController extends Controller
{
   private array $visibleCursors;

   public function __construct()
   {
      //TODO constantes ?
      $this->visibleCursors = [
         'auto80' => true,
         'eval80' => true,
         'auto100' => false,
         'eval100' => false
      ];
   }

/**
 * Store a newly created resource in storage.
 *
 * @param  \App\Http\Requests\StoreEvaluationRequest  $request
 * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
 */
public function storeEvaluation(StoreEvaluationRequest $request)
{
    try {
        // 1. Extraction des données validées
        $evaluationData = $request->input('evaluation_data');

// 2. Traitement de la logique métier (création/mise à jour)
        // La méthode createEvaluation retourne une response()->json()
        $jsonResponse = $this->createEvaluation($evaluationData);

        // 3. Déterminer le type de réponse en fonction de la requête
        if ($request->wantsJson() || $request->expectsJson()) {
            // --- Cas 1 : Requête AJAX/API ---
            // Retourner directement la réponse JSON générée par createEvaluation
            return $jsonResponse;
        } else {
            // --- Cas 2 : Soumission de formulaire classique ---
            
            // a. Extraire les données de la réponse JSON pour vérifier le succès
            // getData(true) convertit l'objet JSON en tableau associatif
            $responseData = $jsonResponse->getData(true); 
            
            if (isset($responseData['success']) && $responseData['success'] === true) {
                // b. Récupération de l'ID pour la redirection
                // L'ID provient des données de la requête, comme vu dans les logs ("ids":"234")
                $ids = $request->input('ids'); 
                
                if (!$ids) {
                     // Gestion d'erreur si 'ids' n'est pas présent
                     Log::warning('ID de redirection manquant dans la requête', ['request_data' => $request->all()]);
                     return redirect()->back()
                                      ->with('error', 'Données de redirection manquantes.')
                                      ->withInput();
                }

                // c. Redirection vers la page souhaitée avec un message de succès
                // Utilisez route() si vous avez une route nommée, ex: route('evaluation.full', ['id' => $ids])
                return redirect()->to("/evaluation/fullEvaluation/{$ids}")
                                 ->with('success', $responseData['message'] ?? 'Évaluation sauvegardée.');
            } else {
                // d. En cas d'erreur lors de la création (bien que le log montre un succès)
                Log::warning('Erreur retournée par createEvaluation lors d\'une requête non-AJAX', ['response_data' => $responseData]);
                return redirect()->back()
                                 ->with('error', $responseData['message'] ?? 'Une erreur est survenue lors de la sauvegarde.')
                                 ->withInput(); // Conserver les données du formulaire
            }
        }

    } catch (\Exception $e) {
        // Journalisation de l'erreur
        Log::error('Erreur lors du traitement de la requête d\'évaluation', [
            'error_message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'request_data' => $request->all(),
        ]);

        // Réponse en fonction du type de requête
        if ($request->wantsJson() || $request->expectsJson()) {
            // --- Erreur pour requête AJAX ---
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création de l\'évaluation.',
                'error' => $e->getMessage()
            ], 500);
        } else {
            // --- Erreur pour soumission classique ---
            return redirect()->back()
                             ->with('error', 'Erreur lors de la création de l\'évaluation: ' . $e->getMessage())
                             ->withInput(); // Conserver les données du formulaire
        }
    }
}


/**
 * Création de l'évaluation
 */
private function createEvaluation(array $data)
{
    // Début de la création de l'évaluation - log de l'entrée
    Log::info('Début de la création d\'une nouvelle évaluation', [
        'data_received_keys' => array_keys($data), // Évite de logger toutes les données sensibles
        'authenticated_user_id' => auth()->id(),
        'timestamp' => now(),
    ]);

    try {
        // Récupération des informations utilisateur et définition des rôles
        $user = auth()->user();
        // Optionnel: $isStudent = $user->hasRole('student');
        // Optionnel: $isTeacher = $user->hasRole('teacher');

        // Vérifier qu'il y a des appréciations
        if (empty($data['appreciations'])) {
            Log::warning('Tentative de création d\'évaluation sans appréciations.');
            throw new \InvalidArgumentException("Aucune appréciation fournie.");
        }

        $evaluationLevel = $data['appreciations'][0]['level'] ?? '';
        Log::info('Niveau de la première appréciation:', [$evaluationLevel]);

        // --- Détermination de l'ID du WorkerContract ---
        // Utiliser worker_contract_id directement depuis $data
        $workerContractId = $data['worker_contract_id'] ?? 0;

        if ($workerContractId <= 0) {
             Log::warning('ID du WorkerContract invalide ou manquant dans les données.', [
                 'worker_contract_id_from_data' => $data['worker_contract_id'] ?? null
             ]);
             throw new \InvalidArgumentException("ID du WorkerContract (worker_contract_id) manquant ou invalide.");
        }
        // --- Fin de la détermination de l'ID ---

        // Vérifier si une évaluation principale existe déjà avec les mêmes identifiants
        // En se basant principalement sur worker_contract_id pour l'unicité logique
        $existingEvaluation = WorkerContractAssessment::where([
            'worker_contract_id' => $workerContractId,
            // Vous pouvez ajouter d'autres conditions si nécessaire
            // 'teacher_id' => $data['evaluator_id'] ?? null,
            // 'student_id' => $data['student_id'] ?? null,
        ])->first();

        $isNewEvaluation = !$existingEvaluation;

        if ($existingEvaluation) {
            // --- Mise à jour d'une évaluation existante ---
            $evaluation = $existingEvaluation;
            Log::info('Évaluation principale existante trouvée', [
                'evaluation_id' => $evaluation->id,
                'worker_contract_id' => $evaluation->worker_contract_id
            ]);
            // Note: Dans ce cas, on ne met à jour que les appréciations/critères.
            // Les champs de l'évaluation principale restent inchangés.
        } else {
            // --- Création d'une nouvelle évaluation principale ---
            $evaluation = new WorkerContractAssessment();
            $evaluation->worker_contract_id = $workerContractId;
            $evaluation->teacher_id    = $data['evaluator_id'] ?? null;
            $evaluation->student_id    = $data['student_id'] ?? null;
            $evaluation->job_id        = $data['job_id'] ?? null;
            $evaluation->class_id      = $data['student_class_id'] ?? null;
            $evaluation->job_title     = $data['job_title'] ?? '';
            $evaluation->status        = ''; // Initialiser le statut
            $evaluation->save();

            Log::info('Nouvelle évaluation principale créée', [
                'evaluation_id' => $evaluation->id,
                'worker_contract_id' => $evaluation->worker_contract_id
            ]);
        }

        // --- Traitement des appréciations et critères ---
        $this->processAppreciations($evaluation, $data['appreciations'], $data['student_remark'] ?? '');
        Log::info('Appréciations et critères traités pour l\'évaluation', [
            'evaluation_id' => $evaluation->id,
            'appreciations_count' => count($data['appreciations']),
        ]);

        // --- Mise à jour du statut (status) SI C'EST UNE NOUVELLE ÉVALUATION ---
        // Cela permet de définir le statut initial basé sur la première appréciation.
        if ($isNewEvaluation) {
            // Déterminer le rôle de l'utilisateur actuel pour la transition
            $userRole = null;
            
            if ($user->hasRole(RoleName::TEACHER)) {
               $userRole = RoleName::TEACHER; 
            } elseif ($user->hasRole(RoleName::STUDENT)) {
               $userRole = RoleName::STUDENT; 
            }

            if ($userRole) {
                // Tenter une transition sur l'objet évaluation nouvellement créée
                // La méthode transition dans WorkerContractAssessment récupère les timings existants
                // et utilise la AssessmentStateMachine pour déterminer le prochain état.
                $transitionSuccess = $evaluation->transition($userRole);

                if ($transitionSuccess) {
                    Log::info('Statut initial de l\'évaluation défini via transition', [
                        'evaluation_id' => $evaluation->id,
                        'new_status_history' => $evaluation->status, // status contient l'historique
                        'user_role' => $userRole
                    ]);
                    // Note: $evaluation->transition() appelle $this->appendStatus() qui sauvegarde l'objet.
                } else {
                    Log::warning('Impossible d\'effectuer une transition d\'état initiale pour cette nouvelle évaluation', [
                        'evaluation_id' => $evaluation->id,
                        'current_statuses' => $evaluation->getStatuses(), // Méthode du modèle
                        'user_role' => $userRole
                    ]);
                }
            } else {
                 Log::warning('Impossible de déterminer le rôle de l\'utilisateur pour la mise à jour du statut initial', [
                     'user_id' => $user->id,
                     'user_roles' => $user->roles->pluck('name')->toArray() ?? 'N/A' // Adapter selon votre système de rôles
                 ]);
            }
        }
        // --- Fin de la mise à jour du statut ---

        // Recharger l'évaluation depuis la base pour s'assurer que 'status' est à jour
        // (normalement inutile car transition() sauvegarde, mais par sécurité)
        // $evaluation->refresh(); // Optionnel

        // --- Préparation et retour de la réponse ---
        $message = $existingEvaluation ?
            'Évaluation mise à jour avec succès.' :
            'Évaluation créée avec succès.';

        // Retourner la réponse en JSON après succès
        // Inclure le 'status' dans les données retournées si nécessaire ailleurs
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $evaluation // L'objet WorkerContractAssessment complet, incluant le 'status' mis à jour
        ], 200);

    } catch (\InvalidArgumentException $e) {
        // Gestion spécifique des erreurs d'argument invalide
        Log::warning('Erreur d\'argument lors du traitement de l\'évaluation', [
            'error_message' => $e->getMessage(),
            'request_data_keys' => array_keys($data ?? []),
            'user_id' => auth()->id(),
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Données d\'évaluation invalides.',
            'error' => $e->getMessage()
        ], 422); // 422 Unprocessable Entity

    } catch (\Exception $e) {
        // Gestion générique des autres erreurs
        Log::error('Erreur fatale lors du traitement de l\'évaluation', [
            'error_message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'request_data_keys' => array_keys($data ?? []), // Éviter de logger des données sensibles
            'user_id' => auth()->id(),
            'timestamp' => now(),
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Une erreur interne est survenue lors du traitement de l\'évaluation.',
            'error' => $e->getMessage() // Retirer en production
        ], 500);
    }
}
/**
 * Traitement des appréciations
 */
private function processAppreciations(WorkerContractAssessment $evaluation, array $appreciations, string $studentRemark = '')
{
    Log::info('processAppreciations appelé', [
        'evaluation_id' => $evaluation->id,
        'student_remark_param' => $studentRemark,
        'student_remark_length' => strlen($studentRemark),
        'appreciations_count' => count($appreciations)
    ]);

    foreach ($appreciations as $index => $appreciationData) {
        Log::info('Traitement appréciation ' . $index, [
            'timing' => $appreciationData['level'],
            'date' => $appreciationData['date'],
            'student_remark_to_save' => $studentRemark
        ]);

        // Vérifier si une assessment existe déjà pour ce timing
        $existingAssessment = Assessment::where([
            'worker_contract_assessment_id' => $evaluation->id,
            'timing' => $appreciationData['level']
        ])->first();

        if ($existingAssessment) {
            // Si une assessment existe déjà pour ce timing, la mettre à jour
            $assessment = $existingAssessment;
            $assessment->date = $appreciationData['date'];
            $assessment->student_remark = $studentRemark;
            $assessment->save();
            
            // Supprimer les critères existants
            $assessment->criteria()->delete();
            Log::info('Assessment existante mise à jour et critères supprimés', [
                'assessment_id' => $assessment->id,
                'timing' => $appreciationData['level']
            ]);
        } else {
            // Sinon, créer une nouvelle assessment
            $assessment = new Assessment();
            $assessment->worker_contract_assessment_id = $evaluation->id;
            $assessment->timing = $appreciationData['level'];
            $assessment->date = $appreciationData['date'];
            $assessment->student_remark = $studentRemark;
            $assessment->save();
            Log::info('Nouvelle Assessment créée', [
                'assessment_id' => $assessment->id,
                'timing' => $appreciationData['level']
            ]);
        }

        // Traitement des critères associés à l'appréciation
        if (isset($appreciationData['criteria']) && is_array($appreciationData['criteria'])) {
            foreach ($appreciationData['criteria'] as $criteriaIndex => $criteriaData) {
                Log::info('Création d\'un critère', [
                    'assessment_id' => $assessment->id,
                    'template_id' => $criteriaData['id'],
                    'value' => $criteriaData['value'],
                ]);

                $criteria = new AssessmentCriterion();
                $criteria->assessment_id = $assessment->id;
                $criteria->timing = $appreciationData['level'];
                $criteria->template_id = $criteriaData['id'];
                $criteria->value = $criteriaData['value'];
                $criteria->checked = $criteriaData['checked'] ?? false;
                $criteria->remark_criteria = $criteriaData['remark'] ?? null;
                $criteria->position = $criteriaIndex + 1;
                $criteria->save();

                Log::info('Critère créé avec succès', [
                    'criteria_id' => $criteria->id,
                    'assessment_id' => $assessment->id,
                    'value' => $criteriaData['value'],
                ]);
            }
        }
        
        Log::info('Assessment traitée avec ses critères', [
            'assessment_id' => $assessment->id,
            'timing' => $assessment->timing,
            'student_remark_saved' => $assessment->student_remark
        ]);
    }

    Log::info('Traitement des appréciations terminé pour l\'évaluation', ['evaluation_id' => $evaluation->id]);
}


// peut-etre plus nécessaire ... 
   private function updateEvaluation(array $data)
   {
      Log::info('Début de la mise à jour de l\'évaluation', ['data' => $data]);
      try {
         // Vérification de l'existence de l'évaluation par la combinaison des champs uniques
         $evaluatorId = $data['evaluator_id'];
         $studentId = $data['student_id'];
         $classId = $data['student_class_id'];
         $jobDefinitionId = $data['job_id'];

         $evaluation = WorkerContractAssessment::where('evaluator_id', $evaluatorId)
            ->where('student_id', $studentId)
            ->where('class_id', $classId)
            ->where('job_definitions_id', $jobDefinitionId)
            ->first();

         if (!$evaluation) {
            throw new \Exception('Évaluation non trouvée pour cette combinaison.');
         }

         Log::info('Évaluation trouvée pour la mise à jour', ['evaluation_id' => $evaluation->id]);

         // Mise à jour des données de l'évaluation
         $oldRemark = $evaluation->student_remark;
         $evaluation->student_remark = $data['student_remark'] ?? $evaluation->student_remark;
         $evaluation->save();

         Log::info('Données de l\'évaluation mises à jour', [
            'old_remark' => $oldRemark,
            'new_remark' => $evaluation->student_remark,
         ]);

         // Mise à jour des appréciations
         foreach ($data['appreciations'] as $appreciationData) {
            // Vérifiez si une appréciation existe pour le niveau spécifié
            $levelIndex = $this->getLevelIndex($appreciationData['level']);
            Log::info('Niveau récupéré via getLevelIndex', [
               'level' => $appreciationData['level'],
               'level_index' => $levelIndex,
            ]);

            $existingAppreciation = Assessment::where('evaluation_id', $evaluation->id)
               ->where('level', $levelIndex)
               ->first();

            if (!$existingAppreciation) {
               // Si l'appréciation pour ce niveau n'existe pas, créez une nouvelle appréciation
               Log::info('Création d\'une nouvelle appréciation', [
                  'evaluation_id' => $evaluation->id,
                  'level' => $levelIndex,
               ]);

               $existingAppreciation = Assessment::create([
                  'evaluation_id' => $evaluation->id,
                  'date' => now(),
                  'level' => $levelIndex,
               ]);
            }

            Log::info('Appréciation insérée/mise à jour avec succès', [
               'evaluation_id' => $evaluation->id,
               'level' => $existingAppreciation->level,
               'criterias' => $appreciationData['criteria'],
            ]);

            // Mise à jour des critères associés à l'appréciation
            foreach ($appreciationData['criteria'] as $criteriaData) {
               // Vérification si le critère existe pour cette appréciation
               $existingCriteria = AssessmentCriterion::where('appreciation_id', $existingAppreciation->id)
                  ->where('id', $criteriaData['id'])
                  ->first();

               if ($existingCriteria) {
                  // Si le critère existe déjà, on le met à jour
                  Log::info('Mise à jour d’un critère existant', [
                     'criteria_id' => $criteriaData['id'],
                     'old_value' => $existingCriteria->value,
                     'new_value' => $criteriaData['value'],
                     'old_position' => $existingCriteria->position,
                     'new_position' => isset($criteriaData['position']) ? $criteriaData['position'] : null,
                  ]);

                  $existingCriteria->value = $criteriaData['value'];
                  $existingCriteria->remark = $criteriaData['remark'];
                  $existingCriteria->checked = $criteriaData['checked'];

                  // Mettre à jour la position uniquement si nécessaire
                  if (isset($criteriaData['position']) && $criteriaData['position'] !== $existingCriteria->position) {
                     Log::info('Position mise à jour pour le critère', [
                        'criteria_id' => $criteriaData['id'],
                        'old_position' => $existingCriteria->position,
                        'new_position' => $criteriaData['id'],
                     ]);
                     $existingCriteria->position = $criteriaData['id'];
                  } else {
                     Log::info('Position inchangée pour le critère', [
                        'criteria_id' => $criteriaData['id'],
                        'current_position' => $existingCriteria->position,
                     ]);
                  }

                  $existingCriteria->save();
               } else {
                  // Si le critère n'existe pas pour cet ID, créer un nouveau critère
                  Log::info('Ajout d’un nouveau critère', [
                     'criteria_data' => $criteriaData,
                     'position' => $criteriaData['id'],
                  ]);

                  AssessmentCriterion::create([
                     'appreciation_id' => $existingAppreciation->id,
                     'name' => $criteriaData['name'],
                     'value' => $criteriaData['value'],
                     'checked' => $criteriaData['checked'],
                     'remark' => $criteriaData['remark'],
                     'position' => isset($criteriaData['id']) ? $criteriaData['id'] : null,
                  ]);

                  Log::info('Nouveau critère ajouté avec succès', [
                     'criteria_id' => $criteriaData['id'],
                  ]);
               }
            }
         }

         Log::info("Évaluation mise à jour avec succès", ['evaluation_id' => $evaluation->id]);

         return response()->json([
            'success' => true,
            'message' => 'Évaluation mise à jour avec succès.',
            'data' => $evaluation,
         ], 200);
      } catch (\Exception $e) {
         Log::error('Erreur lors de la mise à jour de l\'évaluation', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
         ]);
         throw $e; // Propager l'exception pour être gérée plus haut
      }
   }

  


/**
 * Charge les évaluations associées à un contrat de travailleur spécifique.
 *
 * Cette fonction retourne toutes les évaluations principales liées à un contrat de travailleur donné,
 * avec leurs appréciations (Assessment) et critères associés (AssessmentCriterion).
 * Le contrat est identifié par son ID (worker_contract_id).
 *
 * @param int $contractId : ID du contrat de travailleur (worker_contract_id) dont on veut charger les évaluations.
 * @return \Illuminate\Http\JsonResponse : Réponse JSON contenant les évaluations ou un message d'erreur.
 */
public function loadEvaluationsByContract($contractId)
{
    try {
        Log::info('Chargement des évaluations pour le contrat de travailleur ID: ' . $contractId);

        // Récupérer les évaluations principales avec leurs appréciations (assessments) et critères associés
        // worker_contract_id est la clé étrangère dans WorkerContractAssessment pointant vers le contrat
        // assessments est la relation définie dans WorkerContractAssessment vers le modèle Assessment
        // criteria est la relation définie dans Assessment vers le modèle AssessmentCriterion
        $evaluations = WorkerContractAssessment::with(['assessments.criteria'])
            ->where('worker_contract_id', $contractId) // Correction : utiliser worker_contract_id
            ->get();

        // Si aucune évaluation trouvée, retourner un message
        if ($evaluations->isEmpty()) {
            Log::warning("Aucune évaluation trouvée pour le contrat de travailleur ID: {$contractId}");
            return response()->json([
                'success' => false,
                'message' => 'Aucune évaluation trouvée pour ce contrat.',
                // 'data' => [] // Optionnel : vous pouvez aussi retourner un tableau vide data
            ], 404);
        }

        // Retourner les évaluations trouvées
        Log::info("Evaluations trouvées pour le contrat de travailleur ID: {$contractId}", ['count' => $evaluations->count()]);
        return response()->json([
            'success' => true,
            'message' => 'Évaluations chargées avec succès.',
            'data' => $evaluations, // Contient WorkerContractAssessment, leurs Assessments et AssessmentCriteria
        ], 200);

    } catch (\Exception $e) {
        Log::error("Erreur lors du chargement des évaluations pour le contrat de travailleur ID: {$contractId}", [
            'exception_message' => $e->getMessage(),
            'exception_trace' => $e->getTraceAsString()
        ]);

        // Réponse d'erreur cohérente avec storeEvaluation
        return response()->json([
            'success' => false, // Ajout de 'success' => false
            'message' => 'Une erreur est survenue lors du chargement des évaluations.', // Changé 'error' en 'message'
            // 'error' => $e->getMessage() // Optionnel : vous pouvez inclure le message d'exception détaillé
        ], 500);
    }
}

private function getCriteriaGrouped($userCustomId): \Illuminate\Support\Collection
{
    // Vérifier d'abord s'il y a des critères personnalisés
    $hasCustomCriteria = AssessmentCriterionTemplate::where('user_id', $userCustomId)->exists();
    
    $query = AssessmentCriterionTemplate::with('category');
    
    if ($hasCustomCriteria) {
        // Utiliser les critères personnalisés
        $query->where('user_id', $userCustomId);
    } else {
        // Utiliser les critères par défaut (null ou 0)
        $query->where(function($q) {
            $q->whereNull('user_id')
              ->orWhere('user_id', 0);
        });
    }
    
    $criteria = $query->orderBy('position')->get();

    // Regrouper par nom de catégorie
    return $criteria->groupBy(function ($criterion) {
        return $criterion->category ? trim($criterion->category->name) : 'Autres';
    });
}

   public function getCriterias()
   {
      $criterias = AssessmentCriterionTemplate::where('user_id', 0)->get();

      return $criterias;
   }



   /**
    * Récupérer les détails des étudiants associés à un contrat avec leurs informations liées.
    *
    * @param int $contractId
    * @return \Illuminate\Database\Eloquent\Builder
    */
private static function getStudentEvaluationDetailsByContractIds(array $contractIds): \Illuminate\Support\Collection // <= Changement ici
{
    // Vérification préliminaire pour éviter une requête inutile si le tableau est vide.
    if (empty($contractIds)) {
        return collect(); // collect() retourne une Illuminate\Support\Collection
    }

    // Construction de la requête Eloquent en utilisant des jointures.
    return Contract::query() // Utilisation du 'use App\Models\Contract;' pour Contract
        ->join('contract_worker as cw', 'contracts.id', '=', 'cw.contract_id')
        ->join('group_members as gm', 'gm.id', '=', 'cw.group_member_id')
        ->join('users as u', 'u.id', '=', 'gm.user_id')
        ->join('groups as g', 'g.id', '=', 'gm.group_id')
        ->join('group_names as gn', 'gn.id', '=', 'g.group_name_id')
        ->join('job_definitions as jd', 'contracts.job_definition_id', '=', 'jd.id')
        ->leftJoin('contract_client as cc', 'contracts.id', '=', 'cc.contract_id')
        ->leftJoin('users as client', 'cc.user_id', '=', 'client.id')
        ->whereIn('contracts.id', $contractIds)
        ->select(
            'cw.id as worker_contract_id',
            'contracts.id as contract_id',
            'contracts.start as contract_start',
            'contracts.end as contract_end',
            'jd.title as project_name',
            'jd.id as job_id',
            'u.id as student_id',
            'u.firstname as student_firstname',
            'u.lastname as student_lastname',
            'gn.id as class_id',
            'gn.name as class_name',
            'client.id as evaluator_id',
            'client.firstname as evaluator_firstname',
            'client.lastname as evaluator_lastname'
        )
        ->get(); // ->get() retourne une Illuminate\Database\Eloquent\Collection qui est une sous-classe de Illuminate\Support\Collection
}


public function fullEvaluation(string $ids)
{
    $user = auth()->user();
    $isTeacher = $this->checkIfUserIsTeacher($user);
    $studentsDetails = null;

    if ($isTeacher) {
        // Convertir la chaîne des IDs en un tableau
        $idsArray = explode(',', $ids);

        // Récupérer les détails des étudiants liés aux contrats
        $studentsDetailsQuery = $this->getStudentsDetails($idsArray);

        // Si aucun détail n'est trouvé, déclencher une erreur 404
        if ($studentsDetailsQuery->count() === 0) {
            abort(404, 'Contrats non trouvés.');
        }

        // Ajouter la StateMachine ET l'ID du WorkerContract à chaque étudiant
        $studentsDetailsQuery->transform(function ($details) {
            // Vérifier si worker_contract_id existe dans les détails
            $workerContractId = $details->worker_contract_id ?? null;

            if ($workerContractId === null) {
                // Logguer une erreur si l'ID est manquant
                \Log::error('worker_contract_id manquant dans les détails de l\'étudiant', ['details' => $details]);
   
            }

            // Utiliser worker_contract_id pour trouver l'évaluation
            $eval = $this->getExistingAssessmentByWorkerContractId($workerContractId); 

            if ($eval) {
               
               \Log::debug('fullEvaluation: Avant de créer AssessmentStateMachine pour l\'enseignant', [
                  'worker_contract_id' => $workerContractId,
                  'evaluation_id' => $eval->id,
                  'assessments_count' => $eval->assessments->count()
               ]);

                // Et vérifier que la relation est chargée ou la charger
                $details->stateMachine = new AssessmentStateMachine($eval->assessments->toArray()); 
            } else {
                // Si aucune évaluation n'est trouvée
                \Log::warning('fullEvaluation: Aucune évaluation trouvée pour worker_contract_id ' . $workerContractId);
                $details->evaluation_id = null;
                $details->stateMachine = null; 
            }

            // Ajouter worker_contract_id aux détails s'il n'y est pas déjà (normalement il y est grâce à la requête)
            return $details;
        });

        $studentsDetails = $studentsDetailsQuery;

    } else {
        // Cas étudiant
        // $ids est un seul ID de contrat/worker_contract
        $studentsDetails =$this->getStudentEvaluationDetailsByContractIds([$ids]);

        if ($studentsDetails->isNotEmpty()) {
            $studentDetails = $studentsDetails->first();
            
            // --- Utiliser worker_contract_id pour la recherche ---
            $workerContractId = $studentDetails->worker_contract_id ?? (int) $ids; // Fallback sur $ids s'il n'est pas dans les détails
            $eval = $this->getExistingAssessmentByWorkerContractId($workerContractId); 

            // Vérifie que $eval existe
            if ($eval) { 

               \Log::debug('fullEvaluation: Avant de créer AssessmentStateMachine pour l\'étudiant (avec données)', [
               'worker_contract_id' => $workerContractId,
               'evaluation_id' => $eval->id,
               'assessments_count' => $eval->assessments->count()
               ]);

                // Utiliser 'assessments' au lieu de 'appreciations'
                $studentDetails->stateMachine = new AssessmentStateMachine($eval->assessments->toArray()); 
                $studentDetails->evaluation_id = $eval->id;
            } else {
                // Si aucune évaluation n'est trouvée
                $studentDetails->stateMachine = new AssessmentStateMachine([]); // Machine d'état vide
                $studentDetails->evaluation_id = null;
            }
        } else {
            \Log::warning("Aucun détail d'étudiant trouvé pour les ID de contrat : " . json_encode($ids));
            $studentsDetails = collect();
        }
    }

    // Étape 5 : Construire le tableau JSON de sauvegarde
    $allJsonSave = $this->buildJsonSave($studentsDetails);

    // Étape 6 : Renvoyer la vue Blade avec les données, y compris la machine d'état
    return $this->renderEvaluationPage($studentsDetails, $isTeacher, $allJsonSave);
}


   private function getStudentsDetails(array $idsArray)
   {
      return self::getStudentEvaluationDetailsByContractIds($idsArray);
   }


   private function checkIfUserIsTeacher($user): bool
   {
      return $user->hasRole(RoleName::TEACHER);
   }


/**
 * Construit un tableau de données JSON pour la sauvegarde/affichage des évaluations.
 *
 * Prend une collection ou un tableau d'objets étudiants (ou WorkerContracts)
 * et pour chacun, récupère l'évaluation associée (WorkerContractAssessment) depuis la base de données,
 * la transforme en un format standardisé, et compile toutes les données nécessaires.
 *
 * @param mixed $studentOrStudents Un objet, un tableau d'objets, ou une Collection représentant le(s) étudiant(s)/contrat(s).
 * @return array Un tableau formaté contenant les données de chaque étudiant/contrat et son évaluation.
 */
private function buildJsonSave($studentOrStudents): array
{
    // 1. Normalisation de l'entrée en Collection
    // Cela permet de traiter uniformément un seul élément ou plusieurs éléments.
    /** @var Collection $students */
    $students = collect($studentOrStudents);

    // 2. Itération sur chaque élément de la collection
    return $students->map(function ($student) { // $student est l'objet direct
        
        // 3. Détermination de l'ID du WorkerContract
        $workerContractId = $student->student_id ?? $student->id ?? null;

        if (!$workerContractId) {
             \Log::warning('buildJsonSave: Impossible de déterminer le workerContractId pour l\'élément', ['element' => $student]);
             $workerContractId = 0; // Ou gérer l'erreur comme approprié
        } else {
            $workerContractId = (int) $workerContractId;
        }

        // 4. Récupération de l'évaluation existante dans la base de données
        // getExistingAssessment doit être corrigée pour prendre un $workerContractId
        // et utiliser where('worker_contract_id', $workerContractId).
        $existingEvaluations = $this->getExistingAssessmentByWorkerContractId($workerContractId);

        // 5. Transformation de l'évaluation récupérée en un format standardisé
        // mapExistingEvaluations transforme l'objet Eloquent en tableau.
        $evaluationsData = $this->mapExistingEvaluations($existingEvaluations);


        // 6. Compilation des données finales pour cet élément
        // Utilisation de l'opérateur null coalescent (??) pour éviter les erreurs
        // si certaines propriétés de $student sont manquantes.
        return [
            'student_id'         => $student->student_id ?? null,
            'student_lastname'   => $student->student_lastname ?? '',
            'student_firstname'  => $student->student_firstname ?? '',
            'student_class_id'   => $student->class_id ?? null,
            'student_class_name' => $student->class_name ?? '',
            'evaluator_id'       => $student->evaluator_id ?? null,
            'evaluator_name'     => trim(implode('-', [
                $student->evaluator_firstname ?? '',
                $student->evaluator_lastname ?? ''
            ]), '-'),
            'job_id'             => $student->job_id ?? null,
            'job_title'          => $student->project_name ?? '', // Vérifiez le nom exact de la propriété
            'evaluations'        => $evaluationsData, // Sera [] si $existingEvaluations était null
            'project_start'      => $student->contract_start ?? null,
            'project_end'        => $student->contract_end ?? null,
            'contract_id'        => $student->contract_id, 
            'worker_contract_id' => $student->worker_contract_id
            
        ];
    })->toArray(); // 7. Conversion de la Collection résultante en tableau PHP simple
   }

private function getExistingAssessmentByWorkerContractId(int $workerContractId): ?WorkerContractAssessment
{
    return WorkerContractAssessment::where('worker_contract_id', $workerContractId)->first();
}



/*
 * Transforme une évaluation existante en un tableau structuré.
 *
 * @param WorkerContractAssessment|null $existingEvaluation L'évaluation à mapper.
 * @return array Le tableau représentant l'évaluation, ou un tableau vide si $existingEvaluation est null.
 */
private function mapExistingEvaluations(WorkerContractAssessment|null $existingEvaluation): array
{
    // Vérifie si l'évaluation existe
    if ($existingEvaluation === null) {
        \Log::debug('mapExistingEvaluations: Aucune évaluation fournie, retourne tableau vide.');
        return []; // Retourne un tableau vide si aucune évaluation n'est trouvée
    }

    \Log::debug('mapExistingEvaluations: Traitement de l\'évaluation ID ' . $existingEvaluation->id);

    // Charger les appréciations (Assessment) avec leurs critères (AssessmentCriterion) et les templates (AssessmentCriterionTemplate)
    // pour éviter les requêtes N+1
    $assessments = $existingEvaluation->assessments()->with('criteria.template')->get();
    \Log::debug('mapExistingEvaluations: Nombre d\'appréciations trouvées: ' . $assessments->count());

    // Mapper les appréciations
    $mappedAppreciations = $assessments->map(function (Assessment $assessment) {
        \Log::debug('mapExistingEvaluations: Traitement de l\'appréciation ID ' . $assessment->id . ' (Timing: ' . $assessment->timing . ')');
        return [
            'level' => $assessment->timing,
            'date' => $assessment->date ? $assessment->date->toDateTimeString() : null,
            'student_remark' => $assessment->student_remark ?? '',
            'criteria' => $assessment->criteria->map(function ($criterion) {
                \Log::debug('mapExistingEvaluations: Traitement du critère ID ' . $criterion->id . ' (Template ID: ' . $criterion->template_id . ')');
                return [
                    'id' => $criterion->template_id,
                    'name' => $criterion->template->name ?? 'Critère inconnu',
                    'value' => $criterion->value,
                    'checked' => $criterion->checked,
                    'remark' => $criterion->remark_criteria ?? '',
                ];
            })->values(),
        ];
    })->values();

    // Déterminer l'ID de l'évaluateur
    $evaluatorId = $existingEvaluation->teacher_id ?? null;

    // Déterminer la remarque générale de l'étudiant
    $studentRemark = '';
    if ($assessments->isNotEmpty()) {
        $studentRemark = $assessments->first()->student_remark ?? '';
    }

    // --- Calcul de l'état actuel avec la machine à états ---
    // Créer une instance de la machine à états en lui passant les timings des appréciations.
    // La méthode pluck('timing') extrait un tableau des valeurs 'timing' de chaque Assessment.
    // Cela correspond à ce que fait la méthode transition() dans WorkerContractAssessment.
    $stateMachine = new AssessmentStateMachine($assessments->pluck('timing')->toArray());

    // Obtenir l'état actuel calculé par la machine à états
    $currentState = $stateMachine->getCurrentState(); // Retourne un objet \App\Constants\AssessmentState

    // Convertir l'objet état en sa valeur string (ex: 'eval80', 'pending_signature')
    // pour une utilisation plus facile dans la vue ou le frontend.
    $currentStatusValue = $currentState->value;
    // --- Fin du calcul de l'état ---

    // Mappage des données principales de l'évaluation
    $mappedData = [
        'evaluator_id' => $evaluatorId,
        'student_remark' => $studentRemark,
        // Utiliser l'état calculé par la machine à états au lieu de l'historique brut
        'status_eval' => $currentStatusValue, 
        'id_eval' => $existingEvaluation->id,
        'appreciations' => $mappedAppreciations,
    ];

    \Log::debug('mapExistingEvaluations: Mapping terminé', [
        'mapped_data_keys' => array_keys($mappedData),
        'calculated_status' => $currentStatusValue,
        'raw_db_status_history' => $existingEvaluation->status ?? 'N/A'
    ]);
    
    return $mappedData;
}


   private function getInitialVisibleCategories($tabCriterias)
   {
      // Récupérer les catégories visibles
      //$visibleCategories = EvaluationSetting::where('key', 'initialVisibleCategories')->first()->value;

      $visibleCategories = [];
      foreach ($tabCriterias as $key => $value) {
         $visibleCategories[$key] = true;
      }
      return $visibleCategories;
   }

   /**
    * Récupère l'index d'un niveau d'évaluation.
    *
    * @param string $level
    * @return int|null L'index du niveau ou null si non trouvé.
    */
   private function getLevelIndex(string $level): ?int
   {
      Log::info('Recherche de l\'index du niveau d\'évaluation', ['level' => $level]);
      Log::info('Contenu de visibleCursors', ['visibleCursors' => $this->visibleCursors]);

      // Cherche l'index du niveau dans le tableau EVALUATION_LEVELS
      $index = array_search($level, array_keys($this->visibleCursors),);

      // Logguer si l'index a été trouvé ou non
      if (is_int($index)) {
         Log::info('Niveau trouvé, index retourné', ['level' => $level, 'index' => $index]);
      } else {
         Log::warning('Niveau d\'évaluation non trouvé', ['level' => $level, 'index' => $index]);
      }

      // Si $index est un entier valide, on le retourne.
      // Si non trouvé, on retourne null
      return is_int($index) ? $index : null;
   }

   private function renderEvaluationPage($studentsDetails, bool $isTeacher, array $allJsonSave)
   {
      // Récupérer les labels d'appréciation avec une gestion possible du cache pour des performances améliorées.
      $appreciationLabels = $this->getAppreciationLabels();


      // Personnalisation des critères pour la version 2.
      // ⚠️ Le fonctionnement doit être repensé : l'affichage a été optimisé
      // et est désormais généré une seule fois pour tous les étudiants.
      // Cela impacte la logique de personnalisation par utilisateur.
      $userId = $isTeacher ? auth()->id() : 0;


      // Préparer les données à transmettre à la vue
      $viewData = [
         'studentsDatas' => $studentsDetails,
         'visibleCategories' => $this->getInitialVisibleCategories($this->getCriteriaGrouped($userId)),
         'visibleSliders' => $this->visibleCursors,
         'evaluationLevels' => array_keys($this->visibleCursors),
         'appreciationLabels' => $appreciationLabels,
         'criteriaGrouped' => $this->getCriteriaGrouped($userId),
         'isTeacher' => $isTeacher,
         'jsonSave' => $allJsonSave,
      ];
      return view('contracts-fullEvaluation', $viewData);
   }

   private function getAppreciationLabels()
   {
      //TODO cache / constantes
      return ["NA", "PA", "A", "LA"];
   }

   public function updateStatus(FormRequest $request)
   {
      // Journaliser les données entrantes
      Log::info('Mise à jour de l’état d’évaluation initiée', [
         'evaluation_id' => $request->input('evaluation_id'),
         'new_state' => $request->input('new_state'),
         'user_id' => auth()->user()->id ?? 'guest',
      ]);

      // Valider les données entrantes
      $validated = $request->validate([
         'evaluation_id' => 'required|integer|exists:evaluations,id',
         'new_state' => 'required|string|in:not_evaluated,eval80,auto80,eval100,auto100,pending_signature,completed'
      ]);

      Log::info('Données validées avec succès', [
         'validated_data' => $validated,
      ]);

      // Récupérer l'évaluation correspondante
      $evaluation = WorkerContractAssessment::find($validated['evaluation_id']);

      if (!$evaluation) {
         Log::error('Évaluation introuvable', [
            'evaluation_id' => $validated['evaluation_id'],
            'user_id' => auth()->user()->id ?? 'guest',
         ]);
         return response()->json([
            'success' => false,
            'message' => 'Évaluation introuvable.'
         ], 404);
      }

      Log::info('Évaluation trouvée', [
         'evaluation_id' => $evaluation->id,
         'current_status' => $evaluation->status,
      ]);

      // Mettre à jour l'état dans la base de données
      try {
         Log::info('Tentative de mise à jour de l’état de l’évaluation', [
            'evaluation_id' => $evaluation->id,
            'new_status' => $validated['new_state'],
         ]);

         $evaluation->status = $validated['new_state'];
         $evaluation->save();

         Log::info('État de l’évaluation mis à jour avec succès', [
            'evaluation_id' => $evaluation->id,
            'updated_status' => $evaluation->status,
         ]);

         return response()->json([
            'success' => true,
            'message' => 'État de l’évaluation mis à jour avec succès.',
            'data' => [
               'evaluation_id' => $evaluation->id,
               'new_status' => $evaluation->status,
            ],
         ]);
      } catch (\Exception $e) {
         Log::error('Erreur lors de la mise à jour de l’état de l’évaluation', [
            'evaluation_id' => $evaluation->id,
            'new_status' => $validated['new_state'],
            'error_message' => $e->getMessage(),
         ]);

         return response()->json([
            'success' => false,
            'message' => 'Une erreur s’est produite lors de la mise à jour de l’état : ' . $e->getMessage(),
         ], 500);
      }
   }

   public function handleTransition(FormRequest $request)
   {
      $evaluationId = $request->input('evaluationId');
      $userRole = auth()->user()->hasRole(\App\Constants\RoleName::TEACHER) ? 'teacher' : 'student';

      // Log initial pour l'évaluation et le rôle
      Log::info('Début du traitement de la transition', [
         'evaluation_id' => $evaluationId,
         'role' => $userRole
      ]);

      try {
         // Récupération de l'évaluation
         $evaluation = WorkerContractAssessment::with('appreciations')->find($evaluationId);

         // Si l'évaluation n'existe pas, retour avec message d'erreur
         if (!$evaluation) {
            Log::warning('Évaluation introuvable', ['evaluation_id' => $evaluationId]);
            return response()->json(['error' => 'Évaluation non trouvée.'], 404);
         }

         // Validation supplémentaire de l'état de l'évaluation
         if ($evaluation->status === 'completed') {
            Log::warning('Transition impossible: évaluation déjà terminée', ['evaluation_id' => $evaluationId]);
            return response()->json(['error' => 'L\'évaluation est déjà dans un état final.'], 400);
         }

         // Log de l'évaluation trouvée
         Log::info('Évaluation trouvée', [
            'evaluation_id' => $evaluationId,
            'appreciations_count' => $evaluation->appreciations->count()
         ]);

         if ($evaluation->status === 'pending_signature') {
            $evaluation->status = 'completed';
            $evaluation->save();

            Log::info('Évaluation terminée', [
               'evaluation_id' => $evaluationId,
               'appreciations_count' => $evaluation->appreciations->count()
            ]);

            return;
         }

         // Instanciation de la machine d'état
         $evaluationMachine = new AssessmentStateMachine($evaluation->appreciations);


         // Vérification de la possibilité de transition
         if (!$evaluationMachine->canTransition($userRole)) {
            Log::warning('Transition non autorisée', [
               'evaluation_id' => $evaluationId,
               'role' => $userRole
            ]);
            return response()->json(['error' => 'La transition n\'est pas autorisée.'], 403);
         }

         // Log si la transition est autorisée
         Log::info('Transition autorisée', [
            'evaluation_id' => $evaluationId,
            'role' => $userRole,
            'current_state' => $evaluationMachine->getCurrentState()->value,
         ]);

         // Effectuer la transition de l'évaluation
         $passed = $evaluationMachine->transition($userRole);

         // Log de la transition effectuée
         Log::info('Transition effectuée', [
            'evaluation_id' => $evaluationId,
            'transition_result' => $passed ? 'succès' : 'échec'
         ]);

         // Mise à jour de l'état de l'évaluation dans la base de données
         $newState = $evaluationMachine->getCurrentState()->value;

         // Mettre à jour la colonne 'status' dans la base de données
         $evaluation->status = $newState;
         $evaluation->save();

         // Log de la mise à jour de l'évaluation
         Log::info('Évaluation mise à jour', [
            'evaluation_id' => $evaluationId,
            'new_state' => $newState
         ]);

         // Réponse de succès
         return response()->json([
            'success' => true,
            'message' => 'Transition réussie.',
            'newState' => $newState,
         ]);
      } catch (\TypeError $e) {
         // Log des erreurs de type spécifiques
         Log::error('Erreur de type lors de la transition de l\'évaluation', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'evaluation_id' => $evaluationId ?? 'non spécifié',
            'role' => $userRole,
            'error_type' => 'TypeError'
         ]);

         // Retour d'erreur avec message spécifique de type
         return response()->json([
            'error' => 'Erreur de type dans la transition d\'évaluation.',
            'details' => $e->getMessage()
         ], 400);
      } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
         // Erreur d'élément non trouvé
         Log::error('Erreur lors de la récupération de l\'évaluation', [
            'message' => $e->getMessage(),
            'evaluation_id' => $evaluationId ?? 'non spécifié'
         ]);

         return response()->json([
            'error' => 'Évaluation non trouvée.',
            'details' => $e->getMessage()
         ], 404);
      } catch (\Exception $e) {
         // Log des erreurs génériques
         Log::error('Erreur générique lors de la transition', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'evaluation_id' => $evaluationId ?? 'non spécifié',
            'role' => $userRole
         ]);

         // Retour d'erreur générique
         return response()->json([
            'error' => 'Une erreur est survenue lors de la transition.',
            'details' => $e->getMessage()
         ], 500);
      }
   }
}
