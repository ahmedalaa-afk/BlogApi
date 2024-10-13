<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'comment' => $this->comment,
            'comment_slug' => $this->slug,
            'auther' => [
                'name' => $this->user->name,
                'email' => $this->user->email,
            ],
        ];
    }
}
