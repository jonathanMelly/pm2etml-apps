<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ContractEvaluationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can('contracts.evaluate');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'workersContracts' => 'array',
        ];
    }
}
