<?php

namespace App\Http\Resources\uploadMedia;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class show extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'=>$this->id,
            'url'=>$this->url,
            'created_at'=>$this->created_at,
            'updated_at'=>$this->updated_at,
            'posts'=>$this->post
        ];
    }
}