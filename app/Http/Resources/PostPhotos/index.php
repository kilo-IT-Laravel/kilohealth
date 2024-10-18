<?php

namespace App\Http\Resources\PostPhotos;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class index extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'url' => $this->url,
            'post_id' => $this->post_id
        ];
    }
}
