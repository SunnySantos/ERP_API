<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class StockTransferResource extends JsonResource
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
            'product_name' => $this->stock->product->name,
            'quantity' => number_format($this->stock->quantity, 2),
            'branch_receiver_id' => $this->branch_receiver_id,
            'sender' => $this->branch_sender->name,
            'receiver' => $this->branch_receiver->name
        ];
    }
}
