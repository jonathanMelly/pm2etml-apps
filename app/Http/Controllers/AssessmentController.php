<?php

namespace App\Http\Controllers;

// Namespaces Laravel
use App\Constants\RoleName;
use App\Http\Requests\StoreEvaluationRequest;
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
    * Enregistre une nouvelle évaluation pour un étudiant,
    * y compris les appréciations et les critères associés.
    *
    * Cette fonction reçoit une requête POST contenant les données d'évaluation sous forme de JSON.
    * Elle valide et traite les appréciations et les critères associés à chaque étudiant. L'évaluation
    * est ensuite sauvegardée dans la base de données, et les relations avec les critères et appréciations
    * sont établies via des tables associées.
    *
    * En cas de succès, un message de confirmation est renvoyé avec les détails de l'évaluation enregistrée.
    * En cas d'erreur (données invalides ou problème lors de la sauvegarde), un message d'erreur est renvoyé.
    *
    * La transaction est utilisée pour garantir l'intégrité des données en cas d'échec d'une des étapes.
    *
    * @param  \App\Http\Requests\StoreEvaluati onRequest  $request
    * : La requête contenant les données de l'évaluation à enregistrer.
    * @return \Illuminate\Http\JsonResponse
    * : La réponse JSON contenant un message de succès ou d'erreur, selon le résultat de l'opération.
    *
    * @throws \Exception En cas d'erreur lors du traitement ou de la sauvegarde des données.
    */
   public function storeEvaluation(StoreEvaluationRequest $request)
   {
      try {
         Log::info('storeEvaluation démarré', ['data' => $request->all()]);

         // 1. Validation
         $validated = $request->validated();
         Log::info('Données validées', ['data' => $validated]);

         // 2. Récupération propre des données
         $evaluationData = $validated['evaluation_data'] ?? [];
         Log::info('Données d\'évaluation extraites', ['evaluation_data' => $evaluationData]);

         $isUpdate = filter_var($validated['isUpdate'] ?? false, FILTER_VALIDATE_BOOLEAN);
         Log::info('Statut de mise à jour', ['isUpdate' => $isUpdate]);

         // 3. Vérification de la structure d'évaluation
         if (empty($evaluationData)) {
            Log::error('Aucune donnée d\'évaluation reçue');
            throw new \Exception("Aucune donnée d'évaluation reçue.");
         } else {
            Log::info('Données d\'évaluation validées', ['evaluation_data' => $evaluationData]);
         }

         // 4. Nettoyage des appréciations
         if (!isset($evaluationData['appreciations']) || !is_array($evaluationData['appreciations'])) {
            throw new \Exception("Aucune appréciation valide reçue.");
         }

         foreach ($evaluationData['appreciations'] as &$appreciation) {
            if (!isset($appreciation['criteria']) || !is_array($appreciation['criteria'])) {
               throw new \Exception("Appréciation invalide : critères manquants ou mal formés.");
            }

            foreach ($appreciation['criteria'] as &$criterion) {
               $criterion['remark'] = $criterion['remark'] ?? '';
            }
         }

         // 5. Log
         Log::info('isUpdate flag détecté', ['isUpdate' => $isUpdate]);

         DB::beginTransaction();

         // 6. Création ou mise à jour
         if ($isUpdate) {
            Log::info('Mise à jour d\'évaluation en cours', ['evaluationData' => $evaluationData]);
            $response = $this->updateEvaluation($evaluationData);
         } else {
            Log::info('Création d\'évaluation en cours', ['evaluationData' => $evaluationData]);
            $response = $this->createEvaluation($evaluationData);
         }

         DB::commit();

         $ids = $request['ids'] ?? null;
         Log::info('ids = ', ['ids' => $ids]);
         if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Évaluation enregistrée avec succès']);
         } else {
            return redirect()->route('evaluation.fullEvaluation', ['ids' => $ids])
               ->with('success', 'Évaluation enregistrée avec succès');
         }
         return $response;
      } catch (\Illuminate\Validation\ValidationException $e) {
         Log::error('Erreur de validation dans storeEvaluation', ['errors' => $e->errors()]);
         return response()->json(['errors' => $e->errors()], 422);
      } catch (\Exception $e) {
         DB::rollBack();
         Log::error('Erreur dans storeEvaluation', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
         ]);
         return response()->json(['error' => 'Une erreur est survenue lors du traitement.'], 500);
      }
   }

   private function createEvaluation(array $data)
   {
      // Début de la création de l'évaluation - log de l'entrée
      Log::info('Début de la création d\'une nouvelle évaluation', [
         'data_received' => $data,
         'authenticated_user_id' => auth()->user()->id,
         'timestamp' => now(),
      ]);

      try {
         // Récupération des informations utilisateur et définition des rôles
         $user = auth()->user();
         $isStudent = $user->role === 'student';  // Vérifier le rôle de l'utilisateur
         $isTeacher = $user->role === 'teacher';
         $evaluationLevel = $data['appreciations'][0]['level']; // Récupère le niveau d'évaluation

         // Validation pour les étudiants
         if ($isStudent) {
            if (!in_array($evaluationLevel, ['auto80', 'auto100'])) {
               // Log de l'erreur si un étudiant soumet un niveau non autorisé
               Log::error('Un étudiant tente de soumettre un niveau d\'évaluation non autorisé', [
                  'auth_user_id' => $user->id,
                  'evaluation_level' => $evaluationLevel,
                  'role' => 'student',
               ]);
               throw new \InvalidArgumentException("Les étudiants ne peuvent soumettre que des auto-évaluations (auto80 ou auto100).");
            }

            if ($user->id !== $data['student_id']) {
               // Log de l'erreur si un étudiant essaie de soumettre une auto-évaluation pour un autre étudiant
               Log::error('Un étudiant tente de soumettre une auto-évaluation pour un autre étudiant.', [
                  'auth_user_id' => $user->id,
                  'submitted_for_student_id' => $data['student_id'],
               ]);
               throw new \InvalidArgumentException("Un étudiant ne peut soumettre une évaluation que pour lui-même.");
            }
         }

         // Validation pour les enseignants
         if ($isTeacher) {
            if (!in_array($evaluationLevel, ['eval80', 'eval100'])) {
               // Log de l'erreur si un enseignant soumet un niveau non autorisé
               Log::error('Un enseignant tente de soumettre un niveau d\'évaluation non autorisé', [
                  'auth_user_id' => $user->id,
                  'evaluation_level' => $evaluationLevel,
                  'role' => 'teacher',
               ]);
               throw new \InvalidArgumentException("Les enseignants ne peuvent soumettre que des évaluations (eval80 ou eval100).");
            }

            if ($user->id !== $data['evaluator_id']) {
               // Log de l'erreur si un enseignant soumet une évaluation avec un ID incorrect
               Log::error('Un enseignant tente de soumettre une évaluation avec un ID non valide.', [
                  'auth_user_id' => $user->id,
                  'submitted_evaluator_id' => $data['evaluator_id'],
               ]);
               throw new \InvalidArgumentException("Un enseignant ne peut soumettre une évaluation qu'en tant qu'évaluateur autorisé.");
            }
         }

         log::info('Niveau evaluation:', [$evaluationLevel]);

         // Création de l'évaluation dans la base de données
         $evaluation = new WorkerContractAssessment();
         $evaluation->evaluator_id = $data['evaluator_id'];
         $evaluation->student_id = $data['student_id'];
         $evaluation->job_definitions_id = $data['job_id'];
         $evaluation->class_id = $data['student_class_id'];
         $evaluation->student_remark = $data['student_remark'] ?? null;
         // $evaluation->status = 'not_evaluated'; // Par défaut, une évaluation commence avec "not_evaluated"
         $evaluation->save();

         Log::info('Nouvelle évaluation créée avec succès', [
            'evaluation_id' => $evaluation->id,
            'evaluator_id' => $data['evaluator_id'],
            'student_id' => $data['student_id'],
            'job_id' => $data['job_id'],
            'class_id' => $data['student_class_id'],
            'timestamp' => now(),
         ]);

         // Traitement des appréciations et des critères associés à l'évaluation
         $this->processAppreciations($evaluation, $data['appreciations']);
         Log::info('Appréciations et critères traités pour l\'évaluation', [
            'evaluation_id' => $evaluation->id,
            'appreciations_count' => count($data['appreciations']),
            'timestamp' => now(),
         ]);

         // Retourner la réponse en JSON après succès
         return response()->json([
            'success' => true,
            'message' => 'Évaluation créée avec succès.',
            'data' => $evaluation
         ], 200);
      } catch (\Exception $e) {
         // Log de l'erreur lors de la création de l'évaluation
         Log::error('Erreur lors de la création de l\'évaluation', [
            'error_message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'request_data' => $data,
            'timestamp' => now(),
         ]);

         // Propagation de l'exception pour être gérée ailleurs
         throw $e;
      }
   }

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
    * Charge les évaluations associées à un contrat spécifique.
    *
    * @param int $contractId L'ID du contrat dont on veut charger les évaluations.
    * @return \Illuminate\Http\JsonResponse
    */
   private function processAppreciations(WorkerContractAssessment $evaluation, array $appreciations)
   {
      Log::info('Début du traitement des appréciations', ['evaluation_id' => $evaluation->id]);

      foreach ($appreciations as $appreciationData) {
         // Log avant de créer une appréciation
         Log::info('Création de l\'appréciation', [
            'evaluation_id' => $evaluation->id,
            'level' => $appreciationData['level'],
            'date' => $appreciationData['date'],
         ]);

         $appreciation = new Assessment();
         $appreciation->evaluation_id = $evaluation->id;
         $appreciation->level = $this->getLevelIndex($appreciationData['level']);
         $appreciation->date = $appreciationData['date'];
         $appreciation->save();

         Log::info('Appréciation créée avec succès', ['appreciation_id' => $appreciation->id]);

         // Traitement des critères associés à l'appréciation
         foreach ($appreciationData['criteria'] as $criteriaData) {
            // Log avant de créer un critère
            Log::info('Création d\'un critère', [
               'appreciation_id' => $appreciation->id,
               'criteria_id' => $criteriaData['id'],
               'name' => $criteriaData['name'],
               'value' => $criteriaData['value'],
            ]);

            $criteria = new AssessmentCriterion();
            $criteria->appreciation_id = $appreciation->id;
            $criteria->position = $criteriaData['id'];
            $criteria->name = $criteriaData['name'];
            $criteria->value = $criteriaData['value'];
            $criteria->checked = $criteriaData['checked'] ?? 0;
            $criteria->remark = $criteriaData['remark'] ?? null;
            $criteria->save();

            // Log après la création du critère
            Log::info('Critère créé avec succès', [
               'criteria_id' => $criteria->id,
               'appreciation_id' => $appreciation->id,
               'value' => $criteriaData['value'],
            ]);
         }
      }

      Log::info('Traitement des appréciations terminé pour l\'évaluation', ['evaluation_id' => $evaluation->id]);
   }

   /**
    * Charge les évaluations associées à un contrat spécifique.
    *
    * Cette fonction retourne toutes les évaluations liées à un contrat donné, avec leurs appréciations
    * et critères associés. Le contrat est identifié par son ID.
    *
    * @param int $contractId : ID du contrat dont on veut charger les évaluations.
    * @return \Illuminate\Http\JsonResponse : Réponse JSON contenant les évaluations ou un message d'erreur.
    */
   public function loadEvaluationsByContract($contractId)
   {
      try {
         Log::info('Chargement des évaluations pour le contrat ID: ' . $contractId);

         // Récupérer le contrat avec ses évaluations, appréciations, et critères associés
         $evaluations = WorkerContractAssessment::with(['appreciations.criteria'])
            ->where('contract_id', $contractId)
            ->get();

         // Si aucune évaluation trouvée, retourner un message
         if ($evaluations->isEmpty()) {
            Log::warning("Aucune évaluation trouvée pour le contrat ID: {$contractId}");
            return response()->json([
               'success' => false,
               'message' => 'Aucune évaluation trouvée pour ce contrat.',
            ], 404);
         }

         // Retourner les évaluations trouvées
         Log::info("Evaluations trouvées pour le contrat ID: {$contractId}", ['count' => $evaluations->count()]);
         return response()->json([
            'success' => true,
            'message' => 'Évaluations chargées avec succès.',
            'data' => $evaluations,
         ], 200);
      } catch (\Exception $e) {
         Log::error("Erreur lors du chargement des évaluations pour le contrat ID: {$contractId}", [
            'exception_message' => $e->getMessage(),
            'exception_trace' => $e->getTraceAsString()
         ]);

         return response()->json([
            'error' => 'Une erreur est survenue lors du chargement des évaluations.',
         ], 500);
      }
   }

   private function getCriteriaGrouped($userCustomId): \Illuminate\Support\Collection
   {
      // Vérifier si des préférences existent pour cet utilisateur
      $userCriterias = AssessmentCriterionTemplate::where('user_id', $userCustomId)->get();

      // Si des préférences existent, les utiliser
      if ($userCriterias->isNotEmpty()) {
         return $userCriterias->groupBy(fn($crit) => trim($crit['category']));
      }

      // Sinon, utiliser les critères par défaut
      $defaultCriterias = AssessmentCriterionTemplate::where('user_id', 0)->get();

      return $defaultCriterias->groupBy(fn($crit) => trim($crit['category']));
   }


   public function getCriterias()
   {
      $criterias = AssessmentCriterionTemplate::where('user_id', 0)->get();

      return $criterias;
   }


   /**
    * Récupérer les étudiants associés à plusieurs contrats avec leurs informations liées.
    *
    * @param array $contractIds
    * @return \Illuminate\Support\Collection
    */
   private static function getStudentEvaluationDetailsByContractIds(array $contractIds)
   {
      return \App\Models\Contract::join('contract_worker as cw', 'contracts.id', '=', 'cw.contract_id')
         ->join('group_members as gm', 'gm.id', '=', 'cw.group_member_id')
         ->join('users as u', 'u.id', '=', 'gm.user_id')
         ->join('groups as g', 'g.id', '=', 'gm.group_id')
         ->join('group_names as gn', 'gn.id', '=', 'g.group_name_id')
         ->join('job_definitions as jd', 'contracts.job_definition_id', '=', 'jd.id')
         ->leftJoin('contract_client as cc', 'contracts.id', '=', 'cc.contract_id')
         ->leftJoin('users as client', 'cc.user_id', '=', 'client.id')
         ->whereIn('contracts.id', $contractIds)
         ->select(
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
         // Exécuter la requête et retourner une collection d'objets
         ->get();
   }

   /*
    SELECT
    contracts.id AS contract_id,
    contracts.start AS contract_start,
    contracts.end AS contract_end,
    jd.title AS project_name,
    jd.id AS job_id,
    u.id AS student_id,
    u.firstname AS student_firstname,
    u.lastname AS student_lastname,
    gn.id AS class_id,
    gn.name AS class_name,
    client.id AS evaluator_id,
    client.firstname AS evaluator_firstname,
    client.lastname AS evaluator_lastname
      FROM contracts
      JOIN contract_worker AS cw ON contracts.id = cw.contract_id
      JOIN group_members AS gm ON gm.id = cw.group_member_id
      JOIN users AS u ON u.id = gm.user_id
      JOIN groups AS g ON g.id = gm.group_id
      JOIN group_names AS gn ON gn.id = g.group_name_id
      JOIN job_definitions AS jd ON contracts.job_definition_id = jd.id
      LEFT JOIN contract_client AS cc ON contracts.id = cc.contract_id
      LEFT JOIN users AS client ON cc.user_id = client.id
      WHERE contracts.id IN (:contractIds);
   */


   /**
    * Récupérer les détails des étudiants associés à un contrat avec leurs informations liées.
    *
    * @param int $contractId
    * @return \Illuminate\Database\Eloquent\Builder
    */
   private static function getStudentEvaluationDetailsByContractId(int $contractId): \Illuminate\Database\Eloquent\Builder
   {
      if (!is_numeric($contractId)) {
         throw new \InvalidArgumentException("Contract ID must be a valid integer.");
      }

      return \App\Models\Contract::join('contract_worker as cw', 'contracts.id', '=', 'cw.contract_id')
         ->join('group_members as gm', 'gm.id', '=', 'cw.group_member_id')
         ->join('users as u', 'u.id', '=', 'gm.user_id')
         ->join('groups as g', 'g.id', '=', 'gm.group_id')
         ->join('group_names as gn', 'gn.id', '=', 'g.group_name_id')
         ->join('job_definitions as jd', 'contracts.job_definition_id', '=', 'jd.id')
         ->leftJoin('contract_client as cc', 'contracts.id', '=', 'cc.contract_id')
         ->leftJoin('users as client', 'cc.user_id', '=', 'client.id')
         ->where('contracts.id', $contractId)
         ->select(
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
         );
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

         // Ajouter la StateMachine à chaque étudiant
         $studentsDetailsQuery->transform(function ($details) {
            // ajouter peut-etre l'évaluateur ?
            $eval = $this->getExistingAssessment($details->student_id);

            if ($eval) {
               $details->evaluation_id = $eval->id;
               $details->stateMachine = new AssessmentStateMachine($eval->appreciations->toArray());
            } else {
               $details->evaluation_id = null;
               $details->stateMachine = null;
            }

            return $details;
         });

         $studentsDetails = $studentsDetailsQuery;
      } else {

         // // Is student ?
         // $studentsDetails = $this->getStudentEvaluationDetailsByContractId($ids)->get();

         // if ($studentsDetails->isNotEmpty()) {
         //    $studentDetails = $studentsDetails->first();
         //    $eval = $this->getExistingAssessment($studentDetails->student_id);
         //    dd($eval);
         //    $studentDetails->stateMachine = new AssessmentStateMachine($eval->appreciations);
         // } else {
         //    Log::warning("Aucun détail d'étudiant trouvé pour les ID de contrat : " . json_encode($ids));
         //    $studentsDetails = collect();
         // }


         // Is student ?
         $studentsDetails = $this->getStudentEvaluationDetailsByContractId($ids)->get();

         if ($studentsDetails->isNotEmpty()) {
            $studentDetails = $studentsDetails->first();
            $eval = $this->getExistingAssessment($studentDetails->student_id);

            // Vérifie que $eval existe
            if ($eval && $eval->appreciations) {
               $studentDetails->stateMachine = new AssessmentStateMachine($eval->appreciations);
            } else {
               //  Crée une machine d'état vide si aucune évaluation n'existe
               $studentDetails->stateMachine = new AssessmentStateMachine([]);
            }
         } else {
            Log::warning("Aucun détail d'étudiant trouvé pour les ID de contrat : " . json_encode($ids));
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
    * Construire un tableau JSON pour un étudiant ou une liste d'étudiants.
    *
    * @param mixed $studentOrStudents
    * @return array
    * @throws \InvalidArgumentException
    */
   // private function buildJsonSave($studentOrStudents): array
   // {
   //    // Mapper la collection pour construire les données JSON
   //    return $studentOrStudents->map(function ($student) {
   //       // Récupérer les évaluations pour cet étudiant
   //       $existingEvaluations = $this->getExistingEvaluations($student->student_id, $student->job_id);
   //       $evaluationsData = $this->mapExistingEvaluations($existingEvaluations);

   //       // Retourner les données formatées pour cet étudiant
   //       return [
   //          'student_id' => $student->student_id,
   //          'student_lastname' => $student->student_lastname,
   //          'student_firstname' => $student->student_firstname,
   //          'student_class_id' => $student->class_id,
   //          'student_className' => $student->class_name,
   //          'evaluator_id' => $student->evaluator_id,
   //          'evaluator_name' => "{$student->evaluator_firstname}-{$student->evaluator_lastname}",
   //          'job_id' => $student->job_id,
   //          'job_title' => $student->project_name,
   //          'evaluations' => $evaluationsData,
   //       ];
   //    })->toArray();
   // }

   private function buildJsonSave($studentOrStudents): array
   {
      // Assure-toi que $studentOrStudents est une Collection, même si un seul étudiant est passé
      $students = collect($studentOrStudents);

      return $students->map(function ($student) {
         // Récupérer les évaluations pour cet étudiant
         $existingEvaluations = $this->getExistingAssessment($student->student_id);
         $evaluationsData = $this->mapExistingEvaluations($existingEvaluations);

         // Retourner les données formatées pour cet étudiant
         return [
            'student_id'        => $student->student_id,
            'student_lastname'  => $student->student_lastname,
            'student_firstname' => $student->student_firstname,
            'student_class_id'  => $student->class_id,
            'student_class_name' => $student->class_name,
            'evaluator_id'      => $student->evaluator_id,
            'evaluator_name'    => "{$student->evaluator_firstname}-{$student->evaluator_lastname}",
            'job_id'            => $student->job_id,
            'job_title'         => $student->project_name,
            'evaluations'       => $evaluationsData,
            'project_start'              => $student->contract_start,
            'project_end'             => $student->contract_end,
         ];
      })->toArray();
   }



   private function getExistingAssessment($studentId)
   {
      return WorkerContractAssessment::query()->whereRelation('workerContract.groupMember.user', 'id', '=', $studentId)->first();
   }

   private function mapExistingEvaluations(WorkerContractAssessment|null $existingEvaluation)
   {
      // Vérifie si l'évaluation existe
      if ($existingEvaluation === null) {
         return []; // Retourne un tableau vide si aucune évaluation n'est trouvée
      }

      // Mappage des appréciations
      return [
         'evaluator_id' => $existingEvaluation->evaluator()->id,
         'student_remark' => "N/A plus disponible ??",
         'status_eval' => $existingEvaluation->result,
         'id_eval' => $existingEvaluation->id,
         'appreciations' => $existingEvaluation->assessments()->get()->map(function (AssessmentCriterion $appreciation) {
            /* @var $apprecition AssessmentCriterion */
            return [
               'level' => $appreciation->level,
               'date' => $appreciation->date,
               'criteria' => $appreciation->criteria->map(function ($criteria) {
                  return [
                     'id' => $criteria->position,
                     'name' => $criteria->name,
                     'value' => $criteria->value,
                     'checked' => $criteria->checked,
                     'remark' => $criteria->remark,
                  ];
               })->toArray(),
            ];
         })->toArray(),
      ];
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
