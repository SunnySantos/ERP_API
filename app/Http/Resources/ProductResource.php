<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ProductResource extends JsonResource
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
        $file = $this->image;
        if ($this->file_extension === 'bin') {
            $content = Storage::get('public/glb_file/' . $file);
            $exist = Storage::disk('public')->exists('glb_file/' . $file);
            if ($exist) {
                $base64 = "data:application/glb;base64," . base64_encode($content);
            }
        }

        return [
            'id' => $this->id,
            'cake_project_id' => $this->cake_project_id,
            'category_id' => $this->category_id,
            'image' => $this->image,
            'base64' => $base64,
            'file_extension' => $this->file_extension,
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'price_formatted' => '₱' . number_format($this->price, 2),
            'category' => CategoryResource::make($this->category),
            'cake_project' => CakeProjectResource::make($this->cakeProject)
        ];
    }
}
