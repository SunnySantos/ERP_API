<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBasicInformationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
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
        return [
            'firstname' => 'required|string|regex:/^[a-zA-Z\s]*$/',
            'middlename' => 'nullable|string|regex:/^[a-zA-Z\s]*$/',
            'lastname' => 'required|string|regex:/^[a-zA-Z\s]*$/',
            'email' => ['required', 'email', Rule::unique("customers", "email")->ignore($this->id, 'user_id')],
            'phone_number' => ['required', 'string', 'regex:/^9\d{9}$/', Rule::unique("customers", "phone_number")->ignore($this->id, 'user_id')],
            'address' => 'required|string'
        ];
    }
}
