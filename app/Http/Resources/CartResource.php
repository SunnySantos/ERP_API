<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
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
            'product_id' => $this->product_id,
            'order_id' => $this->order_id,
            'dedication' => $this->dedication,
            'product' => ProductResource::make($this->product),
            'quantity' => $this->quantity,
            'quantity_formatted' => number_format($this->quantity, 0),
            'total_price' => $this->total_price,
            'total_price_formatted' => '₱' . number_format($this->total_price, 2)
        ];
    }
}
