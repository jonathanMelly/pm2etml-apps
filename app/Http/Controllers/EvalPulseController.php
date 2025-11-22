<?php

namespace App\Http\Controllers;

use App\Models\AppreciationVersion;
use App\Models\Criterion;
use App\Models\Evaluation;
use App\Models\EvaluationVersion;
use App\Models\Remark;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\EvaluationPdfAttachment;
use Illuminate\Support\Str;
use App\Constants\AttachmentTypes;
use App\Constants\MorphTargets;

class EvalPulseController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'job_definition_id' => 'required|exists:job_definitions,id',
            'worker' => 'required|email|exists:users,email',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $student = User::where('email', $request->worker)->firstOrFail();

        $evaluation = Evaluation::create([
            'eleve_id' => $student->id,
            'teacher_id' => Auth::id(),
            'job_definition_id' => $request->job_definition_id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'status' => 'encours',
        ]);

        return redirect()->route('eval_pulse.show', $evaluation->id);
    }

    public function bulkEvaluate($ids)
    {
        $contractIds = explode(',', $ids);
        $evaluations = collect();

        foreach ($contractIds as $contractId) {
            $workerContract = \App\Models\WorkerContract::with(['contract.jobDefinition', 'groupMember.user'])->find($contractId);

            if (!$workerContract) {
                continue;
            }

            $student = $workerContract->groupMember->user;
            $jobDefinition = $workerContract->contract->jobDefinition;
            
            // Determine teacher ID
            // If current user is the student, teacher is the client of the contract
            if (Auth::id() === $student->id) {
                $teacher = $workerContract->contract->clients->first();
                if (!$teacher) {
                    // Fallback or error handling if no client assigned
                    continue; 
                }
                $teacherId = $teacher->id;
            } else {
                // Current user is the teacher (or admin)
                $teacherId = Auth::id();
            }

            // Find or create the evaluation
            $evaluation = Evaluation::firstOrCreate(
                [
                    'student_id' => $student->id,
                    'teacher_id' => $teacherId,
                    'job_definition_id' => $jobDefinition->id,
                ],
                [
                    'status' => 'encours',
                    'start_date' => $workerContract->contract->start,
                    'end_date' => $workerContract->contract->end,
                ]
            );

            // REPAIR DATA: If teacher_id equals student_id (bug fix), try to repair it
            if ($evaluation->teacher_id === $evaluation->student_id) {
                $realTeacher = $workerContract->contract->clients->first();
                if ($realTeacher && $realTeacher->id !== $evaluation->student_id) {
                    $evaluation->update(['teacher_id' => $realTeacher->id]);
                    $evaluation->teacher_id = $realTeacher->id;
                }
            }

            $evaluations->push($evaluation);
        }

        $evaluations = \Illuminate\Database\Eloquent\Collection::make($evaluations);
        $evaluations->load(['student', 'teacher', 'jobDefinition', 'versions.appreciations.remark', 'versions.generalRemark', 'versions.creator']);
        
        // Auto-correct evaluator_type if missing (migration fix)
        foreach ($evaluations as $evaluation) {
            foreach ($evaluation->versions as $version) {
                if (is_null($version->evaluator_type)) {
                    $type = ($version->created_by_user_id == $evaluation->teacher_id) ? 'teacher' : 'student';
                    $version->update(['evaluator_type' => $type]);
                    $version->evaluator_type = $type;
                }
            }
        }

        $criteria = Criterion::orderBy('position')->get();
        
        // Generate personalized templates for each evaluation and criterion
        $templatesData = [];
        foreach ($evaluations as $evaluation) {
            $fn = $evaluation->student->firstname;
            $ln = $evaluation->student->lastname;
            
            // Templates specific to each criterion
            $templatesData[$evaluation->id] = [];
            
            foreach ($criteria as $criterion) {
                $criterionTemplates = [];
                
                switch($criterion->position) {
                    case 1: // Rythme
                        $criterionTemplates = [
                            (object)['title' => 'Excellent rythme', 'text' => "{$fn} maintient un rythme de travail exemplaire et constant."],
                            (object)['title' => 'Bon rythme', 'text' => "{$fn} travaille à un rythme satisfaisant avec quelques variations."],
                            (object)['title' => 'Rythme irrégulier', 'text' => "{$fn} devrait stabiliser son rythme de travail pour plus d'efficacité."],
                            (object)['title' => 'Trop lent', 'text' => "{$fn} doit accélérer son rythme pour respecter les délais."],
                            (object)['title' => 'Trop rapide', 'text' => "{$fn} devrait ralentir et privilégier la qualité à la vitesse."],
                            (object)['title' => 'Amélioration visible', 'text' => "{$fn} a progressé dans la gestion de son rythme de travail."],
                            (object)['title' => 'Persévère', 'text' => "{$fn}, continue à travailler sur la régularité de ton rythme !"]
                        ];
                        break;
                        
                    case 2: // Qualité
                        $criterionTemplates = [
                            (object)['title' => 'Travail impeccable', 'text' => "{$fn} livre un travail d'une qualité irréprochable."],
                            (object)['title' => 'Bonne qualité', 'text' => "{$fn} produit un travail de bonne qualité avec quelques détails à peaufiner."],
                            (object)['title' => 'Qualité acceptable', 'text' => "{$fn} atteint le niveau de qualité requis mais pourrait faire mieux."],
                            (object)['title' => 'Qualité insuffisante', 'text' => "{$fn} doit accorder plus d'attention à la qualité de son travail."],
                            (object)['title' => 'Manque de soin', 'text' => "{$fn} doit être plus rigoureux et soigner davantage ses réalisations."],
                            (object)['title' => 'Progrès qualité', 'text' => "{$fn} a nettement amélioré la qualité de son travail."],
                            (object)['title' => 'Continue', 'text' => "{$fn}, persiste dans tes efforts pour améliorer la qualité !"]
                        ];
                        break;
                        
                    case 3: // Analyse
                        $criterionTemplates = [
                            (object)['title' => 'Analyse excellente', 'text' => "{$fn} fait preuve d'une remarquable capacité d'analyse."],
                            (object)['title' => 'Bonne analyse', 'text' => "{$fn} analyse correctement les situations avec pertinence."],
                            (object)['title' => 'Analyse superficielle', 'text' => "{$fn} devrait approfondir ses analyses pour plus de pertinence."],
                            (object)['title' => 'Manque d\'analyse', 'text' => "{$fn} doit développer ses compétences d'analyse critique."],
                            (object)['title' => 'Analyse partielle', 'text' => "{$fn} analyse certains aspects mais en oublie d'autres importants."],
                            (object)['title' => 'Progrès analytiques', 'text' => "{$fn} progresse dans sa capacité à analyser les problématiques."],
                            (object)['title' => 'Encourage analyse', 'text' => "{$fn}, continue à questionner et analyser en profondeur !"]
                        ];
                        break;
                        
                    case 4: // Méthodologie
                        $criterionTemplates = [
                            (object)['title' => 'Méthode exemplaire', 'text' => "{$fn} applique une méthodologie rigoureuse et structurée."],
                            (object)['title' => 'Bonne méthode', 'text' => "{$fn} suit une méthodologie appropriée avec quelques ajustements possibles."],
                            (object)['title' => 'Méthode à améliorer', 'text' => "{$fn} devrait adopter une approche plus méthodique."],
                            (object)['title' => 'Méthode désorganisée', 'text' => "{$fn} manque d'organisation et de méthode dans son travail."],
                            (object)['title' => 'Sans méthode', 'text' => "{$fn} doit structurer son approche avec une méthodologie claire."],
                            (object)['title' => 'Progrès méthode', 'text' => "{$fn} s'améliore dans l'application d'une méthodologie de travail."],
                            (object)['title' => 'Structure ton travail', 'text' => "{$fn}, organise-toi avec une méthode claire et efficace !"]
                        ];
                        break;
                        
                    case 5: // Communication
                        $criterionTemplates = [
                            (object)['title' => 'Communication excellente', 'text' => "{$fn} communique de manière claire, précise et professionnelle."],
                            (object)['title' => 'Bonne communication', 'text' => "{$fn} s'exprime bien avec quelques améliorations possibles."],
                            (object)['title' => 'Communication moyenne', 'text' => "{$fn} devrait travailler la clarté de sa communication."],
                            (object)['title' => 'Communication difficile', 'text' => "{$fn} a des difficultés à transmettre ses idées clairement."],
                            (object)['title' => 'Manque clarté', 'text' => "{$fn} doit améliorer la précision et la clarté de ses communications."],
                            (object)['title' => 'Progrès communication', 'text' => "{$fn} progresse dans sa capacité à communiquer efficacement."],
                            (object)['title' => 'Exprime-toi', 'text' => "{$fn}, n'hésite pas à communiquer davantage tes idées !"]
                        ];
                        break;
                        
                    case 6: // Développement durable
                        $criterionTemplates = [
                            (object)['title' => 'Éco-responsable', 'text' => "{$fn} intègre parfaitement les principes du développement durable."],
                            (object)['title' => 'Bonne conscience', 'text' => "{$fn} montre une bonne conscience environnementale dans son travail."],
                            (object)['title' => 'Efforts écologiques', 'text' => "{$fn} devrait mieux considérer l'impact environnemental."],
                            (object)['title' => 'Peu d\'attention', 'text' => "{$fn} ne prend pas assez en compte les aspects durables."],
                            (object)['title' => 'Ignore l\'impact', 'text' => "{$fn} doit intégrer les principes de développement durable."],
                            (object)['title' => 'Sensibilisation', 'text' => "{$fn} commence à considérer les enjeux environnementaux."],
                            (object)['title' => 'Pense durable', 'text' => "{$fn}, pense aux impacts écologiques de tes choix !"]
                        ];
                        break;
                        
                    case 7: // Travail en équipe
                        $criterionTemplates = [
                            (object)['title' => 'Collaboratif exemplaire', 'text' => "{$fn} est un excellent coéquipier qui favorise la collaboration."],
                            (object)['title' => 'Bon esprit d\'équipe', 'text' => "{$fn} travaille bien en équipe avec un bon esprit collaboratif."],
                            (object)['title' => 'Participe peu', 'text' => "{$fn} devrait s'impliquer davantage dans le travail d'équipe."],
                            (object)['title' => 'Difficultés relationnelles', 'text' => "{$fn} a des difficultés à collaborer efficacement avec ses pairs."],
                            (object)['title' => 'Individualiste', 'text' => "{$fn} doit apprendre à travailler de manière plus collaborative."],
                            (object)['title' => 'Progrès équipe', 'text' => "{$fn} s'améliore dans sa capacité à travailler en équipe."],
                            (object)['title' => 'Ensemble', 'text' => "{$fn}, l'équipe est une force, implique-toi davantage !"]
                        ];
                        break;
                        
                    case 8: // Proactivité
                        $criterionTemplates = [
                            (object)['title' => 'Très proactif', 'text' => "{$fn} fait preuve d'une grande proactivité et d'initiative."],
                            (object)['title' => 'Bonne initiative', 'text' => "{$fn} prend des initiatives pertinentes régulièrement."],
                            (object)['title' => 'Peu d\'initiatives', 'text' => "{$fn} devrait davantage prendre des initiatives."],
                            (object)['title' => 'Passif', 'text' => "{$fn} reste trop passif et attend les instructions."],
                            (object)['title' => 'Manque d\'autonomie', 'text' => "{$fn} doit développer son autonomie et sa proactivité."],
                            (object)['title' => 'Progrès autonomie', 'text' => "{$fn} gagne en autonomie et en proactivité."],
                            (object)['title' => 'Ose proposer', 'text' => "{$fn}, n'hésite pas à proposer tes idées et prendre des initiatives !"]
                        ];
                        break;
                        
                    default:
                        $criterionTemplates = [
                            (object)['title' => 'Excellent', 'text' => "{$fn} excelle sur ce critère."],
                            (object)['title' => 'Bien', 'text' => "{$fn} maîtrise bien ce critère."],
                            (object)['title' => 'Satisfaisant', 'text' => "{$fn} atteint un niveau satisfaisant."],
                            (object)['title' => 'À améliorer', 'text' => "{$fn} doit progresser sur ce critère."],
                            (object)['title' => 'Insuffisant', 'text' => "{$fn} n'a pas encore acquis ce critère."],
                            (object)['title' => 'En progrès', 'text' => "{$fn} progresse sur ce critère."],
                            (object)['title' => 'Continue', 'text' => "{$fn}, persévère dans tes efforts !"]
                        ];
                }
                
                $templatesData[$evaluation->id][$criterion->id] = $criterionTemplates;
            }
            
            // General remark templates
            $templatesData[$evaluation->id]['general'] = [
                (object)['title' => 'Excellent travail global', 'text' => "{$fn} a fourni un travail globalement excellent tout au long de cette période. Félicitations pour ton engagement et ta rigueur !"],
                (object)['title' => 'Bon travail général', 'text' => "{$fn} a réalisé un bon travail dans l'ensemble. Continue sur cette voie avec quelques ajustements mineurs."],
                (object)['title' => 'Travail satisfaisant', 'text' => "{$fn} a atteint un niveau satisfaisant sur l'ensemble des critères. Des progrès sont possibles avec plus d'investissement."],
                (object)['title' => 'Effort à poursuivre', 'text' => "{$fn} doit poursuivre ses efforts pour atteindre les objectifs fixés. Certains aspects sont maîtrisés, d'autres nécessitent plus de travail."],
                (object)['title' => 'Progrès constatés', 'text' => "{$fn} a fait des progrès notables durant cette période. Continue dans cette dynamique positive !"],
                (object)['title' => 'Bilan mitigé', 'text' => "{$fn} présente des résultats variables selon les critères. Il est important de consolider les acquis et de travailler les points faibles."],
                (object)['title' => 'Encouragements', 'text' => "{$fn}, tu as montré de la motivation et de l'implication. Continue à t'investir avec la même énergie pour progresser davantage !"]
            ];
        }

        return view('eval_pulse.show', compact('evaluations', 'criteria', 'templatesData'));
    }

    public function update(Request $request, Evaluation $evaluation)
    {
        // Logic to add a new version
        $request->validate([
            'appreciations' => 'required|array',
            'appreciations.*.value' => 'required|in:NA,PA,A,LA',
            'appreciations.*.remark' => 'nullable|string',
            'appreciations.*.is_ignored' => 'nullable|boolean',
            'general_remark' => 'nullable|string',
            'status' => 'nullable|in:encours,clos',
        ]);

        DB::transaction(function () use ($request, $evaluation) {
            // Update evaluation status if provided
            if ($request->filled('status')) {
                $evaluation->update(['status' => $request->status]);
            }

            // Determine evaluator type
            $currentUserId = Auth::id();
            $evaluatorType = ($currentUserId === $evaluation->teacher_id) ? 'teacher' : 'student';

            $generalRemarkId = null;
            if ($request->filled('general_remark')) {
                $remark = Remark::create([
                    'text' => $request->general_remark,
                    'author_user_id' => Auth::id(),
                ]);
                $generalRemarkId = $remark->id;
            }

            // Calculate version number for this evaluator type
            $versionNumber = $evaluation->versions()
                ->where('evaluator_type', $evaluatorType)
                ->max('version_number') + 1;

            $version = EvaluationVersion::create([
                'evaluation_id' => $evaluation->id,
                'version_number' => $versionNumber,
                'evaluator_type' => $evaluatorType,
                'created_by_user_id' => Auth::id(),
                'general_remark_id' => $generalRemarkId,
            ]);

            foreach ($request->appreciations as $criterionId => $data) {
                $remarkId = null;
                if (!empty($data['remark'])) {
                    $remark = Remark::create([
                        'text' => $data['remark'],
                        'author_user_id' => Auth::id(),
                    ]);
                    $remarkId = $remark->id;
                }

                AppreciationVersion::create([
                    'version_id' => $version->id,
                    'criterion_id' => $criterionId,
                    'value' => $data['value'],
                    'remark_id' => $remarkId,
                    'is_ignored' => isset($data['is_ignored']) ? $data['is_ignored'] : false,
                ]);
            }
        });

        return redirect()->back()->with('success', 'Evaluation saved successfully.');
    }

    public function generatePdf(Evaluation $evaluation)
    {
        // 1. Security Check
        if (Auth::id() !== $evaluation->teacher_id) {
            abort(403, 'Unauthorized');
        }

        if ($evaluation->status !== 'clos') {
            return back()->with('error', 'Evaluation must be closed (summative) to generate PDF.');
        }

        // 2. Get Data
        $latestVersion = $evaluation->versions()
            ->where('evaluator_type', 'teacher')
            ->orderByDesc('version_number')
            ->with(['appreciations.remark', 'generalRemark'])
            ->firstOrFail();

        $criteria = Criterion::orderBy('position')->get();

        // 3. Calculate Score (Replicating JS logic)
        $values = $latestVersion->appreciations
            ->where('is_ignored', false)
            ->pluck('value')
            ->toArray();

        $globalScore = '-';
        if (!empty($values)) {
            if (in_array('NA', $values)) $globalScore = 'NA';
            elseif (in_array('PA', $values)) $globalScore = 'PA';
            else {
                $laCount = count(array_filter($values, fn($v) => $v === 'LA'));
                $globalScore = ($laCount >= 4) ? 'LA' : 'A';
            }
        }

        // 4. Generate PDF
        $pdf = Pdf::loadView('eval_pulse.pdf', compact('evaluation', 'latestVersion', 'criteria', 'globalScore'))
            ->setPaper('a4', 'landscape');
        $content = $pdf->output();

        // 5. Encrypt and Store
        $filename = 'eval-' . $evaluation->id . '-' . Str::uuid() . '.pdf';
        $path = 'evaluations/' . $filename;

        $attachment = new EvaluationPdfAttachment();
        $attachment->name = 'Evaluation Summative - ' . now()->format('d.m.Y H:i');
        $attachment->storage_path = $path;
        $attachment->size = strlen($content); 
        $attachment->type = 'application/pdf';
        $attachment->attachable_type = MorphTargets::MORPH2_EVALUATION;
        $attachment->attachable_id = $evaluation->id;
        
        if ($attachment->storeEncrypted($content, $path)) {
            $attachment->save();
            return $pdf->download('Evaluation-' . $evaluation->student->lastname . '.pdf');
        }

        return back()->with('error', 'Failed to save PDF.');
    }
}
