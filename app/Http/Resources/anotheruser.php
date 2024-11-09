<?php

namespace App\Http\Resources;

use App\Traits\getFullThumbnailUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class anotheruser extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    use getFullThumbnailUrl;

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'avatar' => $this->avatar ? $this->getThisUrl($this->avatar) : "https://as2.ftcdn.net/v2/jpg/00/64/67/27/1000_F_64672736_U5kpdGs9keUll8CRQ3p3YaEv2M6qkVY5.jpg",
            'role_id' => $this->role->id,
            'role_name' => $this->role->name
        ];
    }
}
