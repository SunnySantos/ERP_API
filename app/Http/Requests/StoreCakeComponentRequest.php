<?php

namespace App\Http\Requests;

use App\Models\CakeModel;
use App\Rules\CakeModelRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreCakeComponentRequest extends FormRequest
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
            'cake_model_id' => ['required', 'numeric', 'exists:cake_models,id', new CakeModelRule($this->getCakeModel())],
            'name' => 'required|string|max:80',
            'category' => 'required|string',
            'shape' => 'nullable|string|max:80',
            'size' => 'required|string',
            'cost' => 'required|numeric|min:0',
        ];
    }


    public function getCakeModel()
    {
        return CakeModel::where('id', $this->cake_model_id)
            ->whereNull('deleted_at')
            ->first();
    }
}
