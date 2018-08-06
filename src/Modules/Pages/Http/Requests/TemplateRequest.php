<?php

namespace RefinedDigital\CMS\Modules\Pages\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TemplateRequest extends FormRequest
{
    /**
     * Determine if the service is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {

        $args = [
            'name'                => ['required' => 'required'],
            'source'              => ['required' => 'required'],
        ];

        // return the results to set for validation
        return $args;
    }
}
