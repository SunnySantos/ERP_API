<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBasicRequest extends FormRequest
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
            'firstname' => 'required|min:2|string',
            'middlename' => 'nullable|min:2|string',
            'lastname' => 'required|min:2|string',
            'sex' => 'required|string',
            'marital_status' => 'required|string',
            'birth' => 'required|date',
            'address' => 'required|string',
            'phone_number' => ['required', 'string', 'regex:/^9\d{9}$/', Rule::unique("employees", "phone_number")->ignore($this->id)]
        ];
    }
}
