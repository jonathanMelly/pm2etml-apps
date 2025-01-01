<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUpdateJobDefinitionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        //Done with policy
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|bail',
            'description' => 'required|string|bail',
            'required_xp_years' => 'numeric|required',
            'priority' => 'numeric|required',
            'one_shot' => 'sometimes|int|in:1',
            'by_application' => 'sometimes|int|in:1',
            'image' => 'required',
            'other_attachments' => 'json|nullable',
            'any_attachment_to_delete' => 'json|nullable',
            'providers' => 'array|required',
            'skills' => 'json|nullable',
        ];
    }
}
