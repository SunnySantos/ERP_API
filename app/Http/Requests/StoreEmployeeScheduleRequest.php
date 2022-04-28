<?php

namespace App\Http\Requests;

use App\Models\Employee;
use App\Rules\EmployeeModelRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreEmployeeScheduleRequest extends FormRequest
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
            'employee_id' => ['required', 'numeric', 'exists:employees,id', new EmployeeModelRule($this->getEmployee())],
            'schedule_id' => 'required|numeric|exists:schedules,id',
            'attend_date' => 'required|date'
        ];
    }

    public function getEmployee()
    {
        return Employee::where('id', $this->employee_id)
            ->where('branch_id', auth()->user()->employee->branch_id)
            ->whereNull('deleted_at')
            ->first();
    }
}
