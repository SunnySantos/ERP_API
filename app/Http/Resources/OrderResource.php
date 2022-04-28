<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'carts' => CartResource::collection($this->carts),
            'grand_total' => $this->carts->sum('total_price'),
            'grand_total_formatted' => '₱' . number_format($this->carts->sum('total_price'), 2),
            'employee_id' => $this->employee_id,
            'customer_id' => $this->customer_id,
            'customer' => CustomerResource::make($this->customer),
            'branch_id' => $this->branch_id,
            'amount_tendered' => (float) $this->amount_tendered,
            'shipping_fee' => (float) $this->shipping_fee,
            'location' => $this->location,
            'status' => $this->status,
            'created_at' => date('Y-m-d', strtotime($this->created_at)),
            'updated_at' => date('Y-m-d', strtotime($this->updated_at))
        ];
    }
}
