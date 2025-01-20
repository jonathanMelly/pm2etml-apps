<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FullEvaluationRequest extends FormRequest
{
    /**
     * Détermine si l'utilisateur est autorisé à effectuer cette requête.
     */
    public function authorize(): bool
    {
        return $this->user()->can('contracts.evaluate');
    }

    /**
     * Règles de validation pour la requête.
     */
    public function rules(): array
    {
        return [
            'workersContracts' => 'required|array',
            'workersContracts.*.id' => 'exists:worker_contracts,id',
            'workersContracts.*.success' => 'required|boolean',
            'workersContracts.*.comment' => 'nullable|string|max:255',
        ];
    }
}
