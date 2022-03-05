<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CakeIngredientResource extends JsonResource
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
            'ingredient_id' => $this->ingredient_id,
            'cake_component_id' => $this->cake_component_id,
            'amount' => $this->amount,
            'ingredient' => IngredientResource::make($this->ingredient)
        ];
    }
}
