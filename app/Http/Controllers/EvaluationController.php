<?php

namespace App\Http\Controllers;

use App\Models\Evaluation;
use App\Models\Appreciation;
use App\Models\Criteria;
use App\Models\DefaultCriteria;


use App\Http\Requests\StoreEvaluationRequest;

use App\Constants\FullEvaluationConstants;
use App\Constants\RoleName;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;


class EvaluationController extends Controller
{
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

         // Vérification si c'est une mise à jour ou une création
         $evaluationData = $request->input('evaluation_data');
         if (is_string($evaluationData)) {
            $evaluationData = json_decode($evaluationData, true);
         }

         // Déterminer s'il s'agit d'une mise à jour (evaluation_id présent)
         $isUpdate = !empty($evaluationData['isUpdate']);

         DB::beginTransaction();

         if ($isUpdate) {
            $response = $this->updateEvaluation($evaluationData);
         } else {
            $response = $this->createEvaluation($evaluationData);
         }

         DB::commit();
         return $response;
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
      Log::info('Début de la création d\'une nouvelle évaluation', ['data' => $data]);

      try {
         // Validation de l'identité de l'évaluateur
         $studentId = $data['student_Id'];
         $evaluatorId = auth()->user()->id === $data['evaluator_id'] ? $data['evaluator_id'] : null;

         if (!$evaluatorId) {
            Log::error('Évaluateur invalide ou non autorisé', [
               'auth_user_id' => auth()->user()->id,
               'data_evaluator_id' => $data['evaluator_id'],
            ]);
            throw new \InvalidArgumentException("Évaluateur invalide ou non autorisé.");
         }

         // Création d'une nouvelle évaluation
         $evaluation = new Evaluation();
         $evaluation->evaluator_id = $evaluatorId;
         $evaluation->student_id = $studentId;
         $evaluation->job_definitions_id = $data['job_id'];
         $evaluation->class_id = $data['student_classId'];
         $evaluation->student_remark = $data['student_remark'] ?? null;
         $evaluation->save();

         Log::info('Nouvelle évaluation créée avec succès', [
            'evaluation_id' => $evaluation->id,
            'evaluator_id' => $evaluatorId,
            'student_id' => $studentId,
            'job_id' => $data['job_id'],
            'class_id' => $data['student_classId'],
         ]);

         // Traitement des appréciations et critères
         $this->processAppreciations($evaluation, $data['appreciations']);
         Log::info('Appréciations et critères traités pour l\'évaluation', [
            'evaluation_id' => $evaluation->id,
            'appreciations_count' => count($data['appreciations']),
         ]);

         Log::info('Création d\'évaluation terminée avec succès', ['evaluation_id' => $evaluation->id]);
         return response()->json([
            'success' => true,
            'message' => 'Évaluation créée avec succès.',
            'data' => $evaluation
         ], 200);
      } catch (\Exception $e) {
         Log::error('Erreur lors de la création de l\'évaluation', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
         ]);

         throw $e; // Propager l'exception pour être gérée plus haut
      }
   }

   private function updateEvaluation(array $data)
   {
      Log::info('Début de la mise à jour de l\'évaluation', ['data' => $data]);

      try {
         // Vérification de l'existence de l'évaluation par la combinaison des champs uniques
         $evaluatorId = $data['evaluator_id'];
         $studentId = $data['student_Id'];
         $classId = $data['student_classId'];
         $jobDefinitionId = $data['job_id'];

         $evaluation = Evaluation::where('evaluator_id', $evaluatorId)
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
            // Vérifiez si une appréciation existe pour le niveau spécifié (ex: eval80, eval100, etc.)
            $existingAppreciation = Appreciation::where('evaluation_id', $evaluation->id)
               ->where('level', FullEvaluationConstants::getLevelIndex($appreciationData['level'])) // Vérification de l'existence de l'appréciation pour le niveau spécifique
               ->first();

            if (!$existingAppreciation) {
               // Si l'appréciation pour ce niveau n'existe pas, créez une nouvelle appréciation
               $levelIndex = FullEvaluationConstants::getLevelIndex($appreciationData['level']);

               // Log des informations sur le niveau récupéré
               Log::info('Retour getLevelIndex:', ['level' => $appreciationData['level'], 'level_index' => $levelIndex]);

               // Création de l'appréciation
               $existingAppreciation = Appreciation::create([
                  'evaluation_id' => $evaluation->id,
                  'date' => now(),
                  'level' => $levelIndex, // Le niveau (eval80, eval100, etc.)
               ]);
            }

            // Vérification après création
            Log::info('Appréciation insérée avec le niveau', [
               'evaluation_id' => $evaluation->id,
               'level' => $existingAppreciation->level
            ]);

            // Mise à jour ou création des critères associés pour cette appréciation
            foreach ($appreciationData['criteria'] as $criteriaData) {
               // Vérifiez si le critère existe pour cette appréciation au niveau donné
               $existingCriteria = Criteria::where('appreciation_id', $existingAppreciation->id)
                  ->where('id', $criteriaData['id'])  // Vérifiez si le critère existe (ID déjà envoyé dans les données)
                  ->first();

               if ($existingCriteria) {
                  // Si le critère existe déjà, mettez-le à jour
                  $existingCriteria->value = $criteriaData['value'];
                  $existingCriteria->remark = $criteriaData['remark'];
                  $existingCriteria->checked = $criteriaData['checked'];
                  // $existingCriteria->position = $criteriaData['position']; // Mettre à jour la position si nécessaire
                  $existingCriteria->save();
                  Log::info('Critère mis à jour', [
                     'appreciation_id' => $existingAppreciation->id,
                     'criteria_id' => $criteriaData['id']
                  ]);
               } else {
                  // Si le critère n'existe pas pour ce niveau, créez un nouveau critère
                  Criteria::create([
                     'appreciation_id' => $existingAppreciation->id,
                     'name' => $criteriaData['name'],
                     'value' => $criteriaData['value'],
                     'checked' => $criteriaData['checked'],
                     'remark' => $criteriaData['remark'],
                     'position' => $criteriaData['id'], // Positionnement spécifique dans l'appréciation
                  ]);
                  Log::info('Nouveau critère ajouté', [
                     'appreciation_id' => $existingAppreciation->id,
                     'criteria_id' => $criteriaData['id']
                  ]);
               }
            }
         }


         Log::info("Évaluation mise à jour avec succès", ['evaluation_id' => $evaluation->id]);

         return response()->json([
            'success' => true,
            'message' => 'Évaluation mise à jour avec succès.',
            'data' => $evaluation
         ], 200);
      } catch (\Exception $e) {
         Log::error('Erreur lors de la mise à jour de l\'évaluation', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
         ]);

         throw $e; // Propager l'exception pour être gérée plus haut
      }
   }

   private function processAppreciations(Evaluation $evaluation, array $appreciations)
   {
      foreach ($appreciations as $appreciationData) {
         $appreciation = new Appreciation();
         $appreciation->evaluation_id = $evaluation->id;
         $appreciation->level = FullEvaluationConstants::getLevelIndex($appreciationData['level']);
         $appreciation->date = $appreciationData['date'];
         $appreciation->save();

         foreach ($appreciationData['criteria'] as $criteriaData) {
            $criteria = new Criteria();
            $criteria->appreciation_id = $appreciation->id;
            $criteria->position = $criteriaData['id'];
            $criteria->name = $criteriaData['name'];
            $criteria->value = $criteriaData['value'];
            $criteria->checked = $criteriaData['checked'] ?? 0;
            $criteria->remark = $criteriaData['remark'] ?? null;
            $criteria->save();
         }
      }
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
         $evaluations = Evaluation::with(['appreciations.criteria'])
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
      $userCriterias = DefaultCriteria::where('user_id', $userCustomId)->get();

      // Si des préférences existent, les utiliser
      if ($userCriterias->isNotEmpty()) {
         return $userCriterias->groupBy(fn($crit) => trim($crit['category']));
      }

      // Sinon, utiliser les critères par défaut
      $defaultCriterias = DefaultCriteria::where('user_id', 0)->get();

      return $defaultCriterias->groupBy(fn($crit) => trim($crit['category']));
   }


   public function getCriterias()
   {
      $criterias = DefaultCriteria::where('user_id', 0)->get();

      return $criterias;
   }


   /**
    * Récupérer les étudiants associés à plusieurs contrats avec leurs informations liées.
    *
    * @param array $contractIds
    * @return \Illuminate\Support\Collection
    */
   private static function getStudentEvaluationDetailsByContractIds(array $contractIds): \Illuminate\Support\Collection
   {
      return DB::table('contracts as contract')
         ->join('contract_worker as cw', 'contract.id', '=', 'cw.contract_id')
         ->join('group_members as gm', 'gm.id', '=', 'cw.group_member_id')
         ->join('users as u', 'u.id', '=', 'gm.user_id')
         ->join('groups as g', 'g.id', '=', 'gm.group_id')
         ->join('group_names as gn', 'gn.id', '=', 'g.group_name_id')
         ->join('job_definitions as jd', 'contract.job_definition_id', '=', 'jd.id')
         ->leftJoin('contract_client as cc', 'contract.id', '=', 'cc.contract_id') // Table associant les contrats aux clients
         ->leftJoin('users as client', 'cc.user_id', '=', 'client.id') // Lier à la table des utilisateurs pour les clients
         ->whereIn('contract.id', $contractIds)
         ->select(
            'contract.id as contract_id',
            'contract.start as contract_start',
            'contract.end as contract_end',
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
         ->get();
   }

   /**
    * Gère l'affichage de l'évaluation complète pour plusieurs contrats.
    *
    * Cette méthode récupère les détails des contrats liés aux identifiants fournis,
    * détermine si l'utilisateur est enseignant, et prépare les données nécessaires
    * pour afficher la vue Blade d'évaluation complète.
    *
    * @param string $ids Une chaîne contenant les identifiants des contrats, séparés par des virgules.
    * @return \Illuminate\View\View La vue de l'évaluation complète avec les données nécessaires.
    *
    * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException Si aucun contrat n'est trouvé.
    */
   public function fullEvaluation(string $ids)
   {
      // Convertir la chaîne des IDs en un tableau
      $idsArray = explode(',', $ids);

      // Étape 1 : Récupération des détails des étudiants liés aux contrats
      $studentsDetails = $this->getStudentsDetails($idsArray);

      // Si aucun détail n'est trouvé, déclencher une erreur 404
      if ($studentsDetails->isEmpty()) {
         abort(404, 'Contrats non trouvés.');
      }

      // Étape 2 : Déterminer si l'utilisateur connecté est un enseignant
      $user = auth()->user();
      $isTeacher = $this->checkIfUserIsTeacher($user);

      // Étape 3 : Construire un tableau JSON contenant les informations à passer à la vue
      $allJsonSave = $this->buildJsonSave($studentsDetails);

      // Étape 4 : Renvoyer la vue Blade avec les données
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

   private function buildJsonSave($studentsDetails): array
   {
      return $studentsDetails->map(function ($student) {
         $existingEvaluations = $this->getExistingEvaluations($student->student_id, $student->job_id);

         $evaluationsData = $this->mapExistingEvaluations($existingEvaluations);

         return [
            'student_Id' => $student->student_id,
            'student_lastname' => $student->student_lastname,
            'student_firstname' => $student->student_firstname,
            'student_classId' => $student->class_id,
            'student_className' => $student->class_name,
            'evaluator_id' => $student->evaluator_id,
            'evaluator_name' => "{$student->evaluator_firstname}-{$student->evaluator_lastname}",
            'job_id' => $student->job_id,
            'job_title' => $student->project_name,
            'evaluations' => $evaluationsData,
         ];
      })->toArray();
   }

   private function getExistingEvaluations($studentId, $jobId)
   {
      return Evaluation::with(['appreciations.criteria'])
         ->where('student_id', $studentId)
         ->where('job_definitions_id', $jobId)
         ->get();
   }

   private function mapExistingEvaluations($existingEvaluations): array
   {
      return $existingEvaluations->map(function ($evaluation) {
         return [
            'evaluator_id' => $evaluation->evaluator_id,
            'student_remark' => $evaluation->student_remark,
            'appreciations' => $evaluation->appreciations->map(function ($appreciation) {
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
      })->toArray();
   }

   private function getInitialVisibleCategories($tabCriterias)
   {
      $visibleCategories = [];
      foreach ($tabCriterias as $key => $value) {
         $visibleCategories[$key] = true;
      }
      return $visibleCategories;
   }

   private function renderEvaluationPage($studentsDetails, bool $isTeacher, array $allJsonSave)
   {
      return view('contracts-fullEvaluation', [
         'studentsDatas' => $studentsDetails->toArray(),
         'visibleCategories' => $this->getInitialVisibleCategories($this->getCriteriaGrouped($isTeacher ? auth()->user()->id : 0)),
         'visibleSliders' => FullEvaluationConstants::INITIAL_VISIBLE_CURSORS,
         'evaluationLevels' => FullEvaluationConstants::EVALUATION_LEVELS,
         'appreciationLabels' => FullEvaluationConstants::APPRECIATION_LABELS,
         'criteriaGrouped' => $this->getCriteriaGrouped($isTeacher ? auth()->user()->id : 0),
         'isTeacher' => $isTeacher,
         'jsonSave' => $allJsonSave,
      ]);
   }
}
