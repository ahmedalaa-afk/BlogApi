<?php

namespace App\Http\Controllers;

use App\Events\NewCommentNotificationEvent;
use App\Helpers\ApiResponse;
use App\Http\Requests\DeleteCommentRequest;
use App\Http\Requests\StoreCommentRequest;
use App\Http\Resources\CommentResource;
use App\Http\Traits\Slugable;
use App\Models\Comment;
use App\Models\Post;
use App\Notifications\NewCommentNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;

class CommentController extends Controller
{
    use Slugable;
    public function store(StoreCommentRequest $request)
    {

        $post = Post::where('slug', $request->slug)->first();

        $comment = $post->comments()->create([
            'comment' => $request->comment,
            'slug' => $this->slug(),
            'user_id' => $request->user()->id,
        ]);

        Notification::send($post->user,new NewCommentNotification(auth()->user()));
        NewCommentNotificationEvent::dispatch();

        return ApiResponse::sendResponse(201, 'Comment created successfully', new CommentResource($comment));
    }


    public function delete(DeleteCommentRequest $request)
    {

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
