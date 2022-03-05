<?php

namespace App\Rules;

use App\Models\Branch;
use Illuminate\Contracts\Validation\Rule;

class BranchSenderRule implements Rule
{

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
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
        $branch_id = auth()->user()->employee->branch_id;

        $branch_receiver = $this->getBranch($value);

        return $branch_id !== $branch_receiver->id;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The selected :attribute is your branch.';
    }

    public function getBranch($id)
    {
        return Branch::where('id', $id)
            ->whereNull('deleted_at')
            ->first();
    }
}
