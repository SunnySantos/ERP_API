<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class IngredientResource extends JsonResource
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
            'image' => $this->image,
            'name' => $this->name,
            'unit' => $this->unit,
            'cost' => $this->cost,
            'stock' => $this->stock,
            'low_level' => $this->low_level,
            'category' => $this->category
        ];
    }
}
