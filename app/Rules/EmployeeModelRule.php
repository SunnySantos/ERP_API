<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class EmployeeModelRule implements Rule
{
    private $employee;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($employee)
    {
        $this->employee = $employee;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return $this->employee;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return "The :attribute field doesn't belong to organization.";
    }
}
