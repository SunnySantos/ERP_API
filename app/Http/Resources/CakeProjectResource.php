<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CakeProjectResource extends JsonResource
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
            'user_id' => $this->user_id,
            'name' => $this->name,
            'description' => $this->description,
            'cake_project_components' => CakeProjectComponentResource::collection($this->cake_project_components),
        ];
    }
}
