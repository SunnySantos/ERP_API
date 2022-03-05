<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CakeComponentResource extends JsonResource
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
            'cake_model_id' => $this->cake_model_id,
            'name' => $this->name,
            'size' => $this->size,
            'category' => $this->category,
            'shape' => $this->shape,
            'cost' => (float) $this->cost,
            'cost_formatted' => '₱' . number_format($this->cost, 2),
            'cake_model' => CakeModelResource::make($this->cake_model),
            'cake_ingredients' => CakeIngredientResource::collection($this->cake_ingredients)
        ];
    }
}
