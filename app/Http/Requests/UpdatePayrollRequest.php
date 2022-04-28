<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePayrollRequest extends FormRequest
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
            'hour_rate' => 'required|between:0,99.99|min:0',
            'overtime_rate' => 'required|between:0,99.99|min:0',
            'deduction' => 'required|between:0,99.99|min:0',
        ];
    }
}
