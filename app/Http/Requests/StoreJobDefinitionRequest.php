<?php

namespace App\Http\Requests;

use App\Constants\FileFormat;
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
            //These are technical fields, we handle the REQUIRED attribute manually to give a nice message to customer
            'image_data_b64' => 'string|nullable',
            'image_data_b64_ext' => 'string|nullable||in:'.FileFormat::getImageFormatsAsCSV(),
            'providers'=>'array'
        ];
    }
}
