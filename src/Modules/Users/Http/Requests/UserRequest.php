<?php

namespace RefinedDigital\CMS\Modules\Users\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
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
            'first_name'                => ['required' => 'required'],
            'last_name'                 => ['required' => 'required'],
            'email'                     => ['required' => 'required', 'email'       => 'email',     'unique'    => 'unique:users,email'],
            'password'                  => ['required' => 'required', 'confirmed'   => 'confirmed', 'min'       => 'min:5'],
            'password_confirmation'     => ['required' => 'required', 'min'         => 'min:5'],
        ];

        // add the id signinfier to stop the record from over riding the current record
        if ($this->method() == 'PUT' || $this->method() == 'PATCH') {
			$args['email']['unique'] .= ','.$this->route('user');

            // if the password field is empty, then we don't need to validate
            if (!$this->get('password')) {
                unset($args['password']);
                unset($args['password_confirmation']);
            }
        }

        // return the results to set for validation
        return $args;
    }
}
