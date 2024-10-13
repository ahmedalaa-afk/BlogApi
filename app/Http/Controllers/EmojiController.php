<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\StoreEmojiRequest;
use App\Http\Resources\EmojiResource;
use App\Models\Emoji;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EmojiController extends Controller
{
    public function store(StoreEmojiRequest $request)
    {

        // get post
        $post = Post::where('slug', $request->slug)->first();
        
        // check if post exists
        if ($post) {
            // check if post has emoji from user and get it
            $emoji = $post->emoji->where('user_id', $request->user()->id)->first();

            // check if emoji exists for user in the post and update or delete it
            if ($emoji) {
                // if user send diffrent emoji then update it to new emoji
                if ($emoji->emoji != $request->emoji) {
                    $emoji->emoji = $request->emoji;
                    $emoji->save();
                    return ApiResponse::sendResponse(200, 'Emoji updated successfully', new EmojiResource($emoji));
                } 
                // if user send the same emoji then remove it
                elseif ($emoji->emoji == $request->emoji) {
                    $emoji->delete();
                    return ApiResponse::sendResponse(200, 'Emoji removed successfully', []);
                }
            }

            // if emoji not exists for user in the post then create it
            $emoji = $post->emoji()->create([
                'emoji' => $request->emoji,
                'user_id' => auth()->user()->id
            ]);

            return ApiResponse::sendResponse(201, 'Emoji added successfully', new EmojiResource($emoji));
        }
    }
}
