<?php

namespace App\Rules;

use App\Models\Stock;
use Illuminate\Contracts\Validation\Rule;

class StockQuantityRule implements Rule
{


    protected $stock;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(?Stock $stock)
    {
        $this->stock = $stock;
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
        if (is_null($this->stock)) return false;
        return $this->stock->quantity >= $value;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute is greater than stock quantity.';
    }
}
