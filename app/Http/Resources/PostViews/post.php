<?php

namespace App\Http\Resources\PostViews;

use App\Traits\getFullThumbnailUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class post extends JsonResource
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
            'post_id' => $this->post_id,
            'user_id' => $this->user_id,
            'viewed_at' => $this->viewed_at,
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'avatar' => $this->user->avatar ? $this->getThisUrl($this->user->avatar) : "https://as2.ftcdn.net/v2/jpg/00/64/67/27/1000_F_64672736_U5kpdGs9keUll8CRQ3p3YaEv2M6qkVY5.jpg"
            ]
        ];
    }
}
