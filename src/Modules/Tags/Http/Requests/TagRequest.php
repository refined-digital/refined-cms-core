<?php

namespace RefinedDigital\CMS\Modules\Tags\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TagRequest extends FormRequest
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
            'name'              => ['required' => 'required'],
            'type'              => ['required' => 'required', 'not0'],
        ];

        // return the results to set for validation
        return $args;
    }

    public function messages()
    {
        return [
            'type.not0' => 'The type field is required.'
        ];
    }
}
