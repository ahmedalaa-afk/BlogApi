<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $emoji = $this->emoji->pluck('user_id')->contains(auth()->user()->id) ?
            $this->emoji->where('user_id', auth()->user()->id)->first() : null;

        return [
            'content' => $this->content,
            'post slug' => $this->slug,
            'author name' => $this->user->name,
            'author email' => $this->user->email,
            'date' => $this->created_at->diffForHumans(),
            'images' => $this->images->pluck('path'),
            'videos' => $this->videos->pluck('path'),
            'tags' => $this->tags->pluck('name'),
            'user emoji' => $emoji ? [
                'emoji' => $emoji->emoji,
                'owner name' => $emoji->user->name,
                'owner email' => $emoji->user->email,
                'created_at' => $emoji->created_at->diffForHumans(),
            ] : 'no emoji',
            'likes count' => $this->countLikes(),
            'dislikes count' => $this->countDislikes(),
        ];
    }
}
