<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class AssetResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $base64 = null;
        $path = $this->path;
        $content = Storage::get('public/glb_file/' . $path);
        $exist = Storage::disk('public')->exists('glb_file/' . $path);
        if ($exist) {
            $base64 = "data:application/glb;base64," . base64_encode($content);
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'category' => $this->category,
            'cost' => $this->cost,
            'path' => $this->path,
            'shape' => $this->shape,
            'size' => $this->size,
            'base64' => $base64
        ];
    }
}
