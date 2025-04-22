<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateContractRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('contracts.edit');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'start' => 'sometimes|date|before:end',
            'end' => 'sometimes|date|after:start',
            'start_date' => 'sometimes|date|before:end_date',//for remediation
            'end_date' => 'sometimes|date|after:start_date',//for remediation
            'clientId' => 'sometimes|int',
            'remediation-accept' => 'sometimes|int',
        ];
    }
}
