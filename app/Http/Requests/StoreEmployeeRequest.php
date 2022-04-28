<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEmployeeRequest extends FormRequest
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
            'branch_id' => 'required|numeric|exists:branches,id',
            'firstname' => 'required|string|regex:/[A-Za-z]/',
            'middlename' => 'nullable|string|regex:/[A-Za-z]/',
            'lastname' => 'required|string|regex:/[A-Za-z]/',
            'phone_number' => 'required|numeric|unique:employees|regex:/^9\d{9}$/',
            'address' => 'required',
            'sex' => 'required|in:Male,Female',
            'position_id' => 'required|numeric|exists:positions,id',
            'schedule_id' => 'required|numeric|exists:schedules,id',
            'department_id' => 'required|numeric|exists:departments,id',
            'hire' => 'required',
            'birth' => 'required',
            'username' => 'required|string|min:8|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'marital_status' => 'required|string|in:Single,Married,Others'
        ];
    }
}
