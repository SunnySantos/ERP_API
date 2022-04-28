<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAttendanceRequest extends FormRequest
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
            'employee_id' => 'required|numeric|exists:employees,id',
            'attend_date' => 'required|date',
            'time_in' => 'required|date_format:H:i',
            'time_out' => 'required|date_format:H:i'
        ];
    }
}
