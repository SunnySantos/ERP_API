<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:89', Rule::unique("products", "name")->ignore($this->id)],
            'description' => 'required|string|max:240',
            'category_id' => 'required|numeric|exists:categories,id',
            'price' => 'required|numeric|min:0'
        ];
    }
}
