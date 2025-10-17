<?php

namespace App\Http\Requests;

use App\Constants\AssessmentTiming;
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
            'evaluation_data.worker_contract_id' => ['required', 'integer'],
            'evaluation_data.student_remark' => ['nullable', 'string', 'max:10000'],

            // 🔹 Les appréciations utilisent désormais les constantes
            'evaluation_data.evaluations.appreciations' => ['required', 'array'],
            'evaluation_data.evaluations.appreciations.*.date' => ['required', 'date_format:Y-m-d H:i:s'],
            'evaluation_data.evaluations.appreciations.*.level' => [
                'required',
                'string',
                'in:' . implode(',', AssessmentTiming::all()),
            ],
            'evaluation_data.evaluations.appreciations.*.criteria' => ['required', 'array'],
            'evaluation_data.evaluations.appreciations.*.criteria.*.id' => ['required', 'integer'],
            'evaluation_data.evaluations.appreciations.*.criteria.*.name' => ['required', 'string', 'max:255'],
            'evaluation_data.evaluations.appreciations.*.criteria.*.value' => ['required', 'integer', 'min:0', 'max:3'],
            'evaluation_data.evaluations.appreciations.*.criteria.*.checked' => ['required', 'boolean'],
            'evaluation_data.evaluations.appreciations.*.criteria.*.remark' => ['nullable', 'string', 'max:255'],

            'isUpdate' => ['required', 'in:true,false'],
        ];
    }

    public function messages(): array
    {
        return [
            'evaluation_data.required' => 'Les données d\'évaluation sont obligatoires.',
            'evaluation_data.evaluations.appreciations.*.level.in' =>
                'Le niveau doit être : ' . implode(', ', AssessmentTiming::all()) . '.',
            'evaluation_data.evaluations.appreciations.*.criteria.*.value.min' =>
                'La valeur doit être au moins 0.',
            'evaluation_data.evaluations.appreciations.*.criteria.*.value.max' =>
                'La valeur ne peut pas dépasser 3.',
        ];
    }

    protected function prepareForValidation()
    {
        Log::info('Requête reçue', ['full_request' => $this->all()]);

        $raw = $this->input('evaluation_data');

        if (is_string($raw)) {
            $decoded = json_decode($raw, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw ValidationException::withMessages(['evaluation_data' => 'Format JSON invalide.']);
            }
            $this->merge(['evaluation_data' => $decoded]);
            $evaluationData = $decoded;
        } elseif (is_array($raw)) {
            $evaluationData = $raw;
        } else {
            throw ValidationException::withMessages([
                'evaluation_data' => 'Le champ "evaluation_data" doit être un objet ou une chaîne JSON.',
            ]);
        }

        Log::info('Final evaluation_data', ['data' => $evaluationData]);

        // Assure que "evaluations.appreciations" existe
        if (empty($evaluationData['evaluations']['appreciations'])) {
            throw ValidationException::withMessages([
                'evaluation_data' => 'Les appréciations sont manquantes.',
            ]);
        }

        // Validation logique (auto vs eval)
        $this->validateAppreciationLevels($evaluationData['evaluations']['appreciations']);
        $this->validateLevelSeparation($evaluationData['evaluations']['appreciations']);
    }

    protected function validateAppreciationLevels(array $appreciations): void
    {
        $roles = $this->user()->getRoleNames()->toArray();
        $isTeacher = in_array('teacher', $roles);
        $isStudent = in_array('student', $roles);

        foreach ($appreciations as $app) {
            $level = $app['level'] ?? '';

            if ($isTeacher && str_starts_with($level, 'auto')) {
                throw ValidationException::withMessages([
                    'evaluation_data.evaluations.appreciations' =>
                        'Un enseignant ne peut enregistrer que des niveaux "eval".',
                ]);
            }

            if ($isStudent && str_starts_with($level, 'eval')) {
                throw ValidationException::withMessages([
                    'evaluation_data.evaluations.appreciations' =>
                        'Un élève ne peut enregistrer que des niveaux "auto".',
                ]);
            }
        }
    }

    protected function validateLevelSeparation(array $appreciations): void
    {
        $levels = array_column($appreciations, 'level');
        $hasAuto = collect($levels)->contains(fn($l) => str_starts_with($l, 'auto'));
        $hasEval = collect($levels)->contains(fn($l) => str_starts_with($l, 'eval'));

        if ($hasAuto && $hasEval) {
            throw ValidationException::withMessages([
                'evaluation_data.evaluations.appreciations' =>
                    'Impossible de combiner des niveaux "auto" et "eval" dans une même requête.',
            ]);
        }
    }
}
