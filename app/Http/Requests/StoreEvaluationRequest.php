<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

use app\Models\Evaluation;

class StoreEvaluationRequest extends FormRequest
{
    /**
     * Détermine si l'utilisateur est autorisé à effectuer cette demande.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        // Vérifie si l'utilisateur a la permission
        $isAuthorized = $this->user()->can('evaluation.storeEvaluation');

        Log::info('Vérification des permissions utilisateur', [
            'user_id' => $this->user()->id ?? 'guest',
            'has_permission' => $isAuthorized,
            'permissions' => $this->user()->getPermissionsViaRoles()->pluck('name')->toArray(),
        ]);

        return $isAuthorized;
    }

    /**
     * Règles de validation de la requête.
     *
     * @return array
     */
    public function rules(): array
    {
        Log::info('rules method called');
        // Log des données d'entrée
        Log::info('Input data for validation:', [
            'evaluation_data' => $this->input('evaluation_data'),
        ]);

        // Log des règles de validation
        Log::info('Validation rules:', [
            'evaluation_data' => ['required', 'string', 'json'],
        ]);

        /* evaluation_data:  {
        "student_Id":33,
        "student_lastname":"Rochat",
        "student_firstname":"Karine",
        "student_classId":3,
        "student_className":"cin1c",
        "evaluator_id":1,
        "evaluator_name":"prof-esseur",
        "job_id":2,
        "job_title":"Data leakage detection system Version 4.4",
        "student_remark":"",
        "appreciations":[{"date":"2024-12-26T11:08:52.453Z","level":"eval80","criteria":[{"id":1,"name":"Régularité","value":0,"checked":false,"remark":""},{"id":2,"name":"Qualité","value":0,"checked":false,"remark":""},{"id":3,"name":"Maîtrise","value":0,"checked":false,"remark":""},{"id":4,"name":"Autonomie","value":0,"checked":false,"remark":""},{"id":5,"name":"Organisation","value":0,"checked":false,"remark":""},{"id":6,"name":"Compétences","value":0,"checked":false,"remark":""},{"id":7,"name":"Innovation","value":0,"checked":false,"remark":""},{"id":8,"name":"Esprit","value":0,"checked":false,"remark":""}]}]}
        */
        return [
            // Validation du champ id d'évaluation
            'evaluation_data.student_Id' => ['required', 'integer'],
            'evaluation_data.student_lastname' => ['required', 'string', 'max:255'],
            'evaluation_data.student_firstname' => ['required', 'string', 'max:255'],
            'evaluation_data.student_classId' => ['required', 'integer'],
            'evaluation_data.student_className' => ['required', 'string', 'max:255'],
            'evaluation_data.evaluator_id' => ['required', 'integer'],
            'evaluation_data.evaluator_name' => ['required', 'string', 'max:255'],
            'evaluation_data.job_id' => ['required', 'integer'],
            'evaluation_data.job_title' => ['required', 'string', 'max:255'],
            'evaluation_data.student_remark' => ['nullable', 'string', 'max:10000'],

            // Validation pour la section des appréciations
            'evaluation_data.appreciations' => ['required', 'array'],
            'evaluation_data.appreciations.*.date' => ['required', 'date'],
            'evaluation_data.appreciations.*.level' => ['required', 'string', 'in:auto80,eval80,auto100,eval100'],
            'evaluation_data.appreciations.*.criteria' => ['required', 'array'],
            'evaluation_data.appreciations.*.criteria.*.id' => ['required', 'integer'],
            'evaluation_data.appreciations.*.criteria.*.name' => ['required', 'string', 'max:255'],
            'evaluation_data.appreciations.*.criteria.*.value' => ['required', 'integer', 'min:0', 'max:3'],
            'evaluation_data.appreciations.*.criteria.*.checked' => ['required', 'boolean'],
            'evaluation_data.appreciations.*.criteria.*.remark' => ['nullable', 'string', 'max:255'],
            'isUpdate' => ['required', 'in:true,false'],

        ];
    }

    /**
     * Messages personnalisés pour les règles de validation.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'evaluation_data.required' => 'Les données d\'évaluation sont obligatoires. Veuillez fournir toutes les informations nécessaires.',
            'evaluation_data.id.regex' => 'L\'identifiant doit suivre le format correct, tel que "1-88" (par exemple, <strong>année-élève_id</strong>).',
            'evaluation_data.evaluator.required' => 'Le nom de l\'évaluateur est obligatoire. Merci de le fournir.',
            'evaluation_data.appreciations.*.level.in' => 'Le niveau d\'évaluation doit être un des suivants : auto80, eval80, auto100, ou eval100.',
            'evaluation_data.appreciations.*.criteria.*.value.min' => 'La valeur du critère doit être d\'au moins 0.',
            'evaluation_data.appreciations.*.criteria.*.value.max' => 'La valeur du critère ne peut pas dépasser 3. Veuillez entrer une valeur entre 0 et 3.',
            'evaluation_data.appreciations.*.criteria.*.checked.required' => 'Chaque critère doit inclure la case à cocher ("checked"). Assurez-vous qu\'il est défini.',
            'evaluation_data.appreciations.*.criteria.*.remark.max' => 'La remarque peut contenir jusqu\'à 255 caractères maximum.',
        ];
    }


    /**
     * Prépare et valide les données de la requête avant de les soumettre à la validation principale.
     *
     * Cette méthode effectue les opérations suivantes :
     * 1. Vérifie si le champ "evaluation_data" est présent et bien formaté (JSON valide).
     * 2. Décode et journalise les données JSON pour permettre le diagnostic en cas d'erreur.
     * 3. Extrait et ajoute l'ID de l'étudiant ("student_id") à partir de la structure du champ "id".
     * 4. Valide la présence et la structure des appréciations (appreciations) selon le rôle de l'utilisateur :
     *    - Enseignant ("teacher") : Les appréciations doivent inclure le mot-clé "eval".
     *    - Élève ("student") : Les appréciations doivent inclure le mot-clé "auto".
     * 5. Vérifie que chaque critère dans les appréciations contient les champs obligatoires "id" et "value".
     * 6. Enregistre les erreurs éventuelles dans les logs et lance des exceptions en cas de données invalides.
     *
     * @return void
     * @throws ValidationException Si des données manquent, sont mal formatées ou ne respectent pas la structure requise.
     */
    protected function prepareForValidation()
    {
        Log::info('prepareForValidation called');

        // Si "isUpdate" n'est pas fourni, définissez-le par défaut sur false
        $this->merge([
            'isUpdate' => $this->has('isUpdate') ? $this->input('isUpdate') : false,
        ]);

        // Vérifie si le champ "evaluation_data" est manquant avant toute manipulation
        if (!$this->has('evaluation_data')) {
            Log::error('StoreEvaluationRequest Input Missing: No evaluation_data field in the request');
            throw ValidationException::withMessages([
                'evaluation_data' => 'Le champ "evaluation_data" est manquant dans la requête.',
            ]);
        }

        // Journalise la valeur brute de "evaluation_data"
        Log::info('StoreEvaluationRequest Input Data:', [
            'evaluation_data_raw' => $this->input('evaluation_data'),
        ]);

        // Vérifie si 'evaluation_data' existe et si c'est une chaîne
        $evaluationDataJson = $this->input('evaluation_data');
        if (!$evaluationDataJson) {
            Log::error('Données JSON manquantes', ['data' => $this->all()]);
            return response()->json(['error' => 'Données JSON manquantes.'], 400);
        }

        // Essaye de décoder le JSON et vérifier les erreurs
        $evaluationData = json_decode($evaluationDataJson, true);

        // Si le décodage échoue
        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error('Erreur de décodage JSON', ['error' => json_last_error_msg(), 'data' => $evaluationDataJson]);
            return response()->json(['error' => 'Format JSON invalide'], 400);
        }

        // Vérifie si la détection JSON a échoué
        if ($evaluationData === null) {
            Log::error('StoreEvaluationRequest JSON Parsing Error: Invalid JSON format', [
                'raw_data' => $this->input('evaluation_data'),
                'json_error' => json_last_error_msg()
            ]);
            throw ValidationException::withMessages([
                'evaluation_data' => 'Le format du champ "evaluation_data" est invalide ou n’est pas JSON.',
            ]);
        }

        // Log la structure décodée de "evaluation_data"
        Log::info('Decoded evaluation_data:', [
            'evaluation_data' => $evaluationData,
        ]);

        // Ajout du champ "student_id" à partir de l'ID dans le format "teacher-student"
        if (isset($evaluationData['id'])) {
            $parts = explode('-', $evaluationData['id']);
            if (count($parts) === 2 && is_numeric($parts[1])) {
                $studentId = $parts[1];
                $this->merge(['student_id' => $studentId]);
                Log::info('Student ID extracted:', ['student_id' => $studentId]);
            } else {
                Log::error('StoreEvaluationRequest Invalid ID format', [
                    'evaluation_data' => $evaluationData,
                ]);
                throw ValidationException::withMessages([
                    'evaluation_data' => 'Le format de l\'ID est invalide.',
                ]);
            }
        }

        // Vérification de la présence et de la structure des appréciations
        if (!isset($evaluationData['appreciations']) || !is_array($evaluationData['appreciations']) || empty($evaluationData['appreciations'])) {
            Log::error('StoreEvaluationRequest Validation Error: Missing or invalid "appreciations"', [
                'evaluation_data' => $evaluationData,
            ]);
            throw ValidationException::withMessages([
                'evaluation_data' => 'Les données d\'évaluation doivent contenir "appreciations" comme un tableau non vide.',
            ]);
        }

        // Vérification des appréciations selon le rôle de l'utilisateur
        $role = auth()->user()->getRoleNames(); // Supposons que le rôle est déterminé ici
        Log::info('Evaluating "appreciations" based on role', ['role' => $role]);

        foreach ($evaluationData['appreciations'] as $key => $appreciation) {
            // Validation spécifique pour les enseignants
            if ($role === 'teacher') {
                if (empty($appreciation['type']) || !str_contains($appreciation['type'], 'eval')) {
                    Log::error('StoreEvaluationRequest Validation Error: Teacher appreciation missing "eval"', [
                        'appreciation' => $appreciation,
                    ]);
                    throw ValidationException::withMessages([
                        'evaluation_data' => 'Les appréciations doivent contenir "eval" pour un enseignant.',
                    ]);
                }
            }

            // Validation spécifique pour les étudiants
            if ($role === 'student') {
                if (empty($appreciation['type']) || !str_contains($appreciation['type'], 'auto')) {
                    Log::error('StoreEvaluationRequest Validation Error: Student appreciation missing "auto"', [
                        'appreciation' => $appreciation,
                    ]);
                    throw ValidationException::withMessages([
                        'evaluation_data' => 'Les appréciations doivent contenir "auto" pour un élève.',
                    ]);
                }
            }

            // Vérification de la structure de chaque critère dans les appréciations
            if (!isset($appreciation['criteria']) || !is_array($appreciation['criteria'])) {
                Log::error('StoreEvaluationRequest Validation Error: Invalid "criteria" format', [
                    'appreciation' => $appreciation,
                ]);
                throw ValidationException::withMessages([
                    'evaluation_data' => 'Chaque appréciation doit contenir des "criteria" sous forme de tableau.',
                ]);
            }

            // Validation de chaque critère dans l'appréciation
            foreach ($appreciation['criteria'] as $criterion) {
                if (empty($criterion['id']) || !is_int($criterion['id'])) {
                    Log::error('StoreEvaluationRequest Validation Error: Invalid criterion "id"', [
                        'criterion' => $criterion,
                    ]);
                    throw ValidationException::withMessages([
                        'evaluation_data' => 'Le critère doit avoir un "id" valide.',
                    ]);
                }
            }
        }

        // Journalise les données après traitement
        Log::info('StoreEvaluationRequest Processed Data:', [
            'evaluation_data' => $evaluationData,
        ]);

        // Fusionne les données traitées pour les envoyer à la validation
        $this->merge(['evaluation_data' => $evaluationData]);

        // Log des données fusionnées pour la validation
        Log::info('Merged data for validation:', [
            'evaluation_data' => $this->input('evaluation_data'),
        ]);

        // Indique la réussite de la validation
        Log::info('StoreEvaluationRequest Validation Passed');
    }


    
}
