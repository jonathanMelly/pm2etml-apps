<?php

namespace App\Http\Requests;

use Illuminate\Support\Str;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

class StoreEvaluationRequest extends FormRequest
{
    public function authorize(): bool
    {
        $isAuthorized = $this->user()->can('contract.assess');

        Log::info('Vérification des permissions utilisateur', [
            'user_id' => $this->user()->id ?? 'guest',
            'has_permission' => $isAuthorized,
            'permissions' => $this->user()->getPermissionsViaRoles()->pluck('name')->toArray(),
        ]);

        return $isAuthorized;
    }

    public function rules(): array
    {
        return [
            'evaluation_data.student_id' => ['required', 'integer'],
            'evaluation_data.student_lastname' => ['required', 'string', 'max:255'],
            'evaluation_data.student_firstname' => ['required', 'string', 'max:255'],
            'evaluation_data.student_class_id' => ['required', 'integer'],
            'evaluation_data.student_class_name' => ['required', 'string', 'max:255'],
            'evaluation_data.evaluator_id' => ['required', 'integer'],
            'evaluation_data.evaluator_name' => ['required', 'string', 'max:255'],
            'evaluation_data.job_id' => ['required', 'integer'],
            'evaluation_data.job_title' => ['required', 'string', 'max:255'],
            'evaluation_data.student_remark' => ['nullable', 'string', 'max:10000'],

            'evaluation_data.appreciations' => ['required', 'array'],
            'evaluation_data.appreciations.*.date' => ['required', 'date_format:Y-m-d H:i:s'],
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

    public function messages(): array
    {
        return [
            'evaluation_data.required' => 'Les données d\'évaluation sont obligatoires.',
            'evaluation_data.appreciations.*.level.in' => 'Le niveau doit être : auto80, eval80, auto100 ou eval100.',
            'evaluation_data.appreciations.*.criteria.*.value.min' => 'La valeur doit être au moins 0.',
            'evaluation_data.appreciations.*.criteria.*.value.max' => 'La valeur ne peut pas dépasser 3.',
            'evaluation_data.appreciations.*.criteria.*.checked.required' => 'Chaque critère doit inclure un champ "checked".',
            'evaluation_data.appreciations.*.criteria.*.remark.max' => 'La remarque ne peut pas dépasser 255 caractères.',
        ];
    }

    protected function prepareForValidation()
    {
        Log::info('Requête reçue', [
            'full_request' => $this->all(),
        ]);

        $rawEvaluationData = $this->input('evaluation_data');

        if (is_string($rawEvaluationData)) {
            $decoded = json_decode($rawEvaluationData, true);
            Log::info('Decoded evaluation_data', $decoded);

            if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
                Log::error('Erreur JSON', ['message' => json_last_error_msg()]);
                throw ValidationException::withMessages([
                    'evaluation_data' => 'Format JSON invalide.',
                ]);
            }

            $this->merge(['evaluation_data' => $decoded]);
            $evaluationData = $decoded;
        } elseif (is_array($rawEvaluationData)) {
            $evaluationData = $rawEvaluationData;
        } else {
            throw ValidationException::withMessages([
                'evaluation_data' => 'Le champ "evaluation_data" est requis et doit être un objet ou une chaîne JSON.',
            ]);
        }

        Log::info('Final evaluation_data', ['data' => $evaluationData]);

        if (isset($evaluationData['id'])) {
            $parts = explode('-', $evaluationData['id']);
            if (count($parts) === 2 && is_numeric($parts[1])) {
                $this->merge(['student_id' => (int) $parts[1]]);
            } else {
                throw ValidationException::withMessages([
                    'evaluation_data' => 'Format d\'ID invalide.',
                ]);
            }
        }

        if (!isset($evaluationData['appreciations']) || !is_array($evaluationData['appreciations'])) {
            throw ValidationException::withMessages([
                'evaluation_data' => 'Le champ "appreciations" est requis et doit être un tableau.',
            ]);
        }

        $this->validateAppreciationLevels($evaluationData['appreciations']);
        $this->validateLevelSeparation($evaluationData['appreciations']);
    }

    protected function validateAppreciationLevels(array $appreciations): void
    {
        $roles = $this->user()->getRoleNames()->toArray();
        $isTeacher = in_array('teacher', $roles);
        $isStudent = in_array('student', $roles);

        foreach ($appreciations as $app) {
            if (!isset($app['level'])) continue;

            if ($isTeacher && !Str::startsWith($app['level'], 'eval')) {
                throw ValidationException::withMessages([
                    'evaluation_data.appreciations' => 'Un enseignant ne peut enregistrer que des niveaux commençant par "eval".',
                ]);
            }

            if ($isStudent && !Str::startsWith($app['level'], 'auto')) {
                throw ValidationException::withMessages([
                    'evaluation_data.appreciations' => 'Un élève ne peut enregistrer que des niveaux commençant par "auto".',
                ]);
            }
        }
    }

    protected function validateLevelSeparation(array $appreciations): void
    {
        $levels = array_column($appreciations, 'level');
        $hasAuto = collect($levels)->contains(fn($l) => Str::startsWith($l, 'auto'));
        $hasEval = collect($levels)->contains(fn($l) => Str::startsWith($l, 'eval'));

        if ($hasAuto && $hasEval) {
            throw ValidationException::withMessages([
                'evaluation_data.appreciations' => 'Impossible de combiner des niveaux "auto" et "eval" dans une même requête.',
            ]);
        }
    }
}
