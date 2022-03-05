<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ExpenseReportResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'created_at' => date('Y-m-d', strtotime($this->created_at)),
            'name' => $this->name,
            'amount' => '₱' . number_format($this->amount, 2)
        ];
    }
}
