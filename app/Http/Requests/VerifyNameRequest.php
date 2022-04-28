<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VerifyNameRequest extends FormRequest
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
            'firstname' => 'required|string|regex:/^[a-zA-ZñÑ\s]+$/',
            'middlename' => 'nullable|string|regex:/^[a-zA-ZñÑ\s]+$/',
            'lastname' => 'required|string|regex:/^[a-zA-ZñÑ\s]+$/'
            // 'address' => 'required|string',
            // 'phone_number' => 'required|string|unique:customers|regex:/^9\d{9}$/',
            // 'username' => 'required|min:8|string|unique:users',
            // 'password' => 'required|min:8|string|confirmed',
        ];
    }
}
