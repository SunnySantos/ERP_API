<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBranchRequest extends FormRequest
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
            'name' => 'required|string|regex:/^[A-Za-z\s]*$/',
            'address' => 'required|string',
            'phone_number' => ['required', 'string', 'regex:/^9\d{9}$/', Rule::unique("branches", "phone_number")->ignore($this->id)],
        ];
    }
}
