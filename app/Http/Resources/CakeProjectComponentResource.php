<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CakeProjectComponentResource extends JsonResource
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
            'cake_project_id' => $this->cake_project_id,
            'cake_component_id' => $this->cake_component_id,
            'uuid' => $this->uuid,
            'posX' => (float) $this->posX,
            'posY' => (float) $this->posY,
            'posZ' => (float) $this->posZ,
            'cake_component' => CakeComponentResource::make($this->cake_component)
        ];
    }
}
