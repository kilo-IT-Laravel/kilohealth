<?php

namespace App\Http\Resources\Post;

use App\Traits\getFullThumbnailUrl;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class publishShow extends JsonResource
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
            'title' => $this->title,
            'content' => $this->content,
            'views' => $this->views,
            'likes' => $this->likes,
            'category' => $this->whenLoaded('category', function () {
                return [
                    'id' => $this->category->id,
                    'name' => $this->category->name,
                    'slug' => $this->category->slug,
                    'icon' => $this->getThisUrl($this->category->icon),
                ];
            }),
            'author' => $this->whenLoaded('author', function () {
                return [
                    'id' => $this->author->id,
                    'name' => $this->author->name,
                    'email' => $this->author->email,
                    'avatar' => $this->author->avatar ? $this->getThisUrl($this->author->avatar) : "https://as2.ftcdn.net/v2/jpg/00/64/67/27/1000_F_64672736_U5kpdGs9keUll8CRQ3p3YaEv2M6qkVY5.jpg",
                ];
            }),
            'published_at' => $this->getFormattedDate($this->published_at),
            'read_time_text' => $this->read_time == 1 ? '1 minute read' : "{$this->read_time} minutes read",
            'thumbnail' => $this->getThisUrl($this->thumbnail),
            'description' => $this->description,
        ];
    }

    private function getFormattedDate($date): ?string
    {
        return $date ? Carbon::parse($date)->format('F d, Y') : null;
    }
}
