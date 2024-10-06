<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Resources\CommentResource;
use App\Http\Traits\Slugable;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CommentController extends Controller
{
    use Slugable;
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'slug' => ['required', 'string', 'exists:posts,slug'],
            'comment' => ['required', 'string'],
        ], [], [
            'slug' => 'Slug',
            'comment' => 'Comment',
        ]);

        if ($validator->fails()) {
            return ApiResponse::sendResponse(400, 'Validation Error', $validator->errors()->messages());
        }

        $post = Post::where('slug', $request->slug)->first();

        $comment = $post->comments()->create([
            'comment' => $request->comment,
            'slug' => $this->slug(),
            'user_id' => $request->user()->id,
        ]);

        return ApiResponse::sendResponse(201, 'Comment created successfully', new CommentResource($comment));
    }


    public function delete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'slug' => ['required', 'string', 'exists:comments,slug'],
        ], [], [
            'slug' => 'Slug',
        ]);

        if ($validator->fails()) {
            return ApiResponse::sendResponse(400, 'Validation Error', $validator->errors()->messages());
        }

        $comment = Comment::where('slug', $request->slug)->first();

        if ($request->user()->id != $comment->user->id) {
            return ApiResponse::sendResponse(401, 'Unauthorized', []);
        }

        if ($comment) {
            $comment->delete();
            return ApiResponse::sendResponse(200, 'Comment deleted successfully',[]);
        }

        return ApiResponse::sendResponse(404, 'Comment not found',[]);
    }
}
