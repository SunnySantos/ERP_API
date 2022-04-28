<?php

namespace App\Rules;

use App\Models\CakeModel;
use Illuminate\Contracts\Validation\Rule;

class CakeModelRule implements Rule
{

    public $cakeModel;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(?CakeModel $cakeModel)
    {
        $this->cakeModel = $cakeModel;
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
        return $this->cakeModel;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The selected :attribute is invalid.';
    }
}
