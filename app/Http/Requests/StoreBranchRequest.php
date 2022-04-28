<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBranchRequest extends FormRequest
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
            'name' => 'required|unique:branches|string|regex:/^[A-Za-z\s]*$/',
            'address' => 'required|string',
            'phone_number' => 'required|numeric|regex:/^9\d{9}$/|unique:branches'
        ];
    }
}
