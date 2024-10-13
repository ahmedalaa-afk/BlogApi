<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmojiResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'emoji' => $this->emoji,
            'owner' => [
                'name' => $this->user->name,
                'email' => $this->user->email,
            ],
            'post_slug' => $this->emojiable->slug
        ];
    }
}
