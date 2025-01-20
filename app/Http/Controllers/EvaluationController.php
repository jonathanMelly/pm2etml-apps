<?php

namespace App\Http\Controllers;

use App\Models\Evaluation;
use App\Models\Appreciation;
use App\Models\Criteria;
use App\Models\DefaultCriteria;

use App\Models\EvaluationSetting;
use App\Models\EvaluationStateMachine;
use App\Models\EvaluationLevel;


use App\Http\Requests\StoreEvaluationRequest;

use App\Constants\RoleName;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class EvaluationController extends Controller
{
   private $visibleCursors;


   public function __construct()
   {
      $this->visibleCursors = EvaluationSetting::getVisibleCursors();
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

            if ($user->id !== $data['student_Id']) {
               // Log de l'erreur si un étudiant essaie de soumettre une auto-évaluation pour un autre étudiant
               Log::error('Un étudiant tente de soumettre une auto-évaluation pour un autre étudiant.', [
                  'auth_user_id' => $user->id,
                  'submitted_for_student_id' => $data['student_Id'],
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

         // Création de l'évaluation dans la base de données
         $evaluation = new Evaluation();
         $evaluation->evaluator_id = $data['evaluator_id'];
         $evaluation->student_id = $data['student_Id'];
         $evaluation->job_definitions_id = $data['job_id'];
         $evaluation->class_id = $data['student_classId'];
         $evaluation->student_remark = $data['student_remark'] ?? null; // Gère les remarques optionnelles
         $evaluation->save();

         Log::info('Nouvelle évaluation créée avec succès', [
            'evaluation_id' => $evaluation->id,
            'evaluator_id' => $data['evaluator_id'],
            'student_id' => $data['student_Id'],
            'job_id' => $data['job_id'],
            'class_id' => $data['student_classId'],
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


   /**
    * Met à jour une évaluation existante dans la base de données.
    *
    * @param array $data Les nouvelles données d'évaluation.
    * @return \Illuminate\Http\JsonResponse
    */
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
               // Vérification de l'existence de l'appréciation pour le niveau spécifique
               ->where('level', $this->getLevelIndex($appreciationData['level']))
               ->first();

            if (!$existingAppreciation) {
               // Si l'appréciation pour ce niveau n'existe pas, créez une nouvelle appréciation
               $levelIndex = $this->getLevelIndex($appreciationData['level']);

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

            foreach ($appreciationData['criteria'] as $criteriaData) {
               // Vérification si le critère existe pour cette appréciation
               $existingCriteria = Criteria::where('appreciation_id', $existingAppreciation->id)
                  ->where('id', $criteriaData['id'])
                  ->first();

               if ($existingCriteria) {
                  // Si le critère existe déjà, on le met à jour
                  $existingCriteria->value = $criteriaData['value'];
                  $existingCriteria->remark = $criteriaData['remark'];
                  $existingCriteria->checked = $criteriaData['checked'];
                  // Mettre à jour la position uniquement si nécessaire
                  if (isset($criteriaData['position']) && $criteriaData['position'] !== $existingCriteria->position) {
                     $existingCriteria->position = $criteriaData['position'];  // Mettre à jour si position change
                  }
                  $existingCriteria->save();

                  Log::info('Critère mis à jour', [
                     'appreciation_id' => $existingAppreciation->id,
                     'criteria_id' => $criteriaData['id'],
                     'updated_fields' => ['value', 'remark', 'checked', 'position']
                  ]);
               } else {
                  // Si le critère n'existe pas pour cet ID, créer un nouveau critère
                  Criteria::create([
                     'appreciation_id' => $existingAppreciation->id,
                     'name' => $criteriaData['name'],
                     'value' => $criteriaData['value'],
                     'checked' => $criteriaData['checked'],
                     'remark' => $criteriaData['remark'],
                     'position' => isset($criteriaData['position']) ? $criteriaData['position'] : null,  // Assurer qu'il y a bien une position
                  ]);
                  Log::info('Nouveau critère ajouté', [
                     'appreciation_id' => $existingAppreciation->id,
                     'criteria_id' => $criteriaData['id'],
                     'new_criteria_data' => $criteriaData
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

   /**
    * Charge les évaluations associées à un contrat spécifique.
    *
    * @param int $contractId L'ID du contrat dont on veut charger les évaluations.
    * @return \Illuminate\Http\JsonResponse
    */
   private function processAppreciations(Evaluation $evaluation, array $appreciations)
   {
      Log::info('Début du traitement des appréciations', ['evaluation_id' => $evaluation->id]);

      foreach ($appreciations as $appreciationData) {
         // Log avant de créer une appréciation
         Log::info('Création de l\'appréciation', [
            'evaluation_id' => $evaluation->id,
            'level' => $appreciationData['level'],
            'date' => $appreciationData['date'],
         ]);

         $appreciation = new Appreciation();
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

            $criteria = new Criteria();
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
   private static function getStudentEvaluationDetailsByContractIds(array $contractIds): \Illuminate\Database\Eloquent\Builder
   {
      return \App\Models\Contract::join('contract_worker as cw', 'contracts.id', '=', 'cw.contract_id')
         ->join('group_members as gm', 'gm.id', '=', 'cw.group_member_id')
         ->join('users as u', 'u.id', '=', 'gm.user_id')
         ->join('groups as g', 'g.id', '=', 'gm.group_id')
         ->join('group_names as gn', 'gn.id', '=', 'g.group_name_id')
         ->join('job_definitions as jd', 'contracts.job_definition_id', '=', 'jd.id')
         ->leftJoin('contract_client as cc', 'contracts.id', '=', 'cc.contract_id') // Table associant les contrats aux clients
         ->leftJoin('users as client', 'cc.user_id', '=', 'client.id') // Lier à la table des utilisateurs pour les clients
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
         );
   }


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

         // Étape 1 : Récupération des détails des étudiants liés aux contrats
         $studentsDetailsQuery = $this->getStudentsDetails($idsArray);

         // Si aucun détail n'est trouvé, déclencher une erreur 404
         if ($studentsDetailsQuery->count() === 0) {
            abort(404, 'Contrats non trouvés.');
         }
         // Étape 3 : Ajouter la StateMachine à chaque étudiant
         $studentsDetails = $studentsDetailsQuery->paginate(16);

         // Étape 4 : Construire un tableau JSON contenant les informations à passer à la vue
         $studentsDetails->getCollection()->transform(function ($student) use ($user) {

            // Vérifie si une évaluation existe pour cet étudiant et cet enseignant
            $evaluation = $this->getExistingEvaluations($student->student_id, $student->job_id);

            if ($evaluation) {
               // Si une évaluation existe, associer son ID et sa machine d'état à l'étudiant
               $student->evaluation_id = $evaluation->id;
               // On crée une machine d'état sans base de données en passant les appréciations
               $student->stateMachine = new EvaluationStateMachine($evaluation->id, $evaluation->appreciations->toArray());
            } else {
               // Si aucune évaluation n'existe, gérer ce cas (ex. null)
               $student->evaluation_id = null;
               $student->stateMachine = null;
            }

            return $student;
         });
      } else {
         $studentsDetails = collect([$this->getStudentEvaluationDetailsByContractId($ids)->first()]);

         if ($studentsDetails->isNotEmpty()) {
            $student = $studentsDetails[0];

            $evaluation = $this->getExistingEvaluations($student->student_id, $student->job_id);
            if ($evaluation) {
               $student->stateMachine = new EvaluationStateMachine($evaluation->id, $evaluation->appreciations->toArray());
            } else {
               $student->stateMachine = new EvaluationStateMachine();
            }
         } else {
            // Gérer le cas où les détails de l'étudiant ne sont pas trouvés
            // Par exemple, vous pouvez initialiser une collection vide ou enregistrer une entrée de journal
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
   private function buildJsonSave($studentOrStudents): array
   {
      // Mapper la collection pour construire les données JSON
      return $studentOrStudents->map(function ($student) {
         // Récupérer les évaluations pour cet étudiant
         $existingEvaluations = $this->getExistingEvaluations($student->student_id, $student->job_id);
         $evaluationsData = $this->mapExistingEvaluations($existingEvaluations);

         // Retourner les données formatées pour cet étudiant
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
      // Utilise first() pour obtenir le premier élément ou null si vide
      return Evaluation::with(['appreciations.criteria'])
         ->where('student_id', $studentId)
         ->where('job_definitions_id', $jobId)
         ->first();
   }

   private function mapExistingEvaluations($existingEvaluation)
   {
      // Vérifie si l'évaluation existe
      if ($existingEvaluation === null) {
         return []; // Retourne un tableau vide si aucune évaluation n'est trouvée
      }

      // Mappage des appréciations
      return [
         'evaluator_id' => $existingEvaluation->evaluator_id,
         'student_remark' => $existingEvaluation->student_remark,
         'appreciations' => $existingEvaluation->appreciations->map(function ($appreciation) {
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

      // Identifier l'ID de l'utilisateur pour obtenir les critères
      $userId = $isTeacher ? auth()->user()->id : 0;

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
      // Récupérer les labels d'appréciation, gérer le cache si nécessaire.
      return EvaluationSetting::where('key', 'appreciationLabels')
         ->first()
         ?->value ?? [];
   }
}
