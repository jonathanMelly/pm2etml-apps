<?php

namespace App\Http\Requests;

use App\Constants\RoleName;
use Illuminate\Foundation\Http\FormRequest;

class StoreContractRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return
            $this->user()->can('jobs-apply') /*standard student*/ ||
            ($this->has("worker") && $this->user()->hasRole(RoleName::TEACHER)) /*teacher makes a manual enrolment*/;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'job_definition_id'=>'required|int',
            'worker'=>'string|email'
        ];
    }
}
