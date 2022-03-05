<?php

namespace App\Http\Requests;

use App\Models\Branch;
use App\Models\Stock;
use App\Rules\BranchSenderRule;
use App\Rules\StockQuantityRule;
use Illuminate\Foundation\Http\FormRequest;

class StockTransferRequest extends FormRequest
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
            'branch_receiver_id' => ['required', 'numeric', 'exists:branches,id', new BranchSenderRule],
            'stock_id' => 'required|numeric|exists:stocks,id',
            'quantity' => ['required', 'numeric', 'min:1', new StockQuantityRule($this->getStockModel())],
        ];
    }

    public function getStockModel()
    {
        $branch_id = auth()->user()->employee->branch_id;
        return Stock::where('branch_id', $branch_id)
            ->where('id', $this->stock_id)
            ->whereNull('deleted_at')
            ->first();
    }
}
