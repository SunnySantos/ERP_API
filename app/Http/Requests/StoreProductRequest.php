<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
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
            'image' => 'required',
            'name' => 'required|string|unique:products|max:80|regex:/^[a-zA-Z\s]*$/',
            'description' => 'required|string|max:240',
            'category_id' => 'required|numeric|exists:categories,id',
            'price' => 'required|numeric|min:0'
        ];
    }
}
