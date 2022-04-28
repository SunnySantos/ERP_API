<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateIngredientRequest extends FormRequest
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
            'name' => 'required|string',
            'unit' => 'required|string',
            'stock' => 'required|numeric|min:0',
            'low_level' => 'required|numeric|min:0',
            'cost' => 'required|numeric|min:0',
            'category' => 'required|string'
        ];
    }
}
