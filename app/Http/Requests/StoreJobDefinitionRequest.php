<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreJobDefinitionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        //Done with policy
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name'=>'string|required',
            'description'=>'string|required',
            'required_xp_years'=>'numeric|required',
            'priority'=>'numeric|required',
            'image_data' => 'image|required|mimes:jpg,png,jpeg,gif,svg,tiff',
            'providers'=>'array'
        ];
    }
}
