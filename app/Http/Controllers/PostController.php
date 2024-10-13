<?php

namespace App\Http\Controllers;

use App\Events\SendFollowerNotificationEvent;
use App\Events\SendUserCreatePostNotificationEvent;
use App\Helpers\ApiResponse;
use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Http\Resources\FavoriteResource;
use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Http\Traits\Slugable;
use App\Notifications\FollowersNotification;
use App\Notifications\UserCreatePostNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Spatie\Tags\Tag;

class PostController extends Controller
{
    use Slugable;
    public function index()
    {
        $posts = Post::orderBy('created_at', 'desc')->paginate(5);
        if (count($posts) > 0) {
            if ($posts->total() > $posts->perPage()) {
                $data = [
                    'data' => PostResource::collection($posts),
                    'pagination_links' => [
                        'current_page' => $posts->currentPage(),
                        'per_page' => $posts->perPage(),
                        'total' => $posts->total(),
                        'links' => [
                            'first_page' => $posts->url(1),
                            'next_page' => $posts->nextPageUrl(),
                            'prev_page' => $posts->previousPageUrl(),
                            'last_page' => $posts->url($posts->lastPage()),
                        ],
                    ],
                ];
            } else {
                $data = PostResource::collection($posts);
            }
            return ApiResponse::sendResponse(200, 'Posts retrieved successfully', $data);
        }
        return ApiResponse::sendResponse(404, 'No posts found', []);
    }

    public function store(StorePostRequest $request)
    {
        if (empty($request->content) && empty($request->photo) && empty($request->video)) {
            return ApiResponse::sendResponse(400, 'Please Enter Content or Photo or Video', []);
        }

        $data = [
            'user_id' => auth()->user()->id,
            'slug' => $this->slug(),
        ];
        $post = Post::create($data);

        if (!empty($request->content)) {
            $data['content'] = $request->content;
        }

        if ($request->hasFile('photo')) {
            $photo = $request->file('photo');

            foreach ($photo as $file) {
                $photoName = uuid_create() . '-' . time() . '.' . $file->getClientOriginalExtension();

                $file->storeAs('public/posts/images', $photoName);

                $post->images()->create([
                    'path' => Storage::url($photoName)
                ]);
            }
        }
        if ($request->hasFile('video')) {
            $video = $request->file('video');

            foreach ($video as $file) {
                $videoName = uuid_create() . '-' . time() . '.' . $file->getClientOriginalExtension();

                $file->storeAs('public/posts/videos', $videoName);

                $post->videos()->create([
                    'path' => Storage::url($videoName)
                ]);
            }
        }
        if (!empty($request->tags)) {
            $tags = explode(' ', $request->tags);
            $normalizedTags = array_map(function ($tag) {
                return strtolower(ltrim($tag, '#'));
            }, $tags);
            $post->attachTags($normalizedTags);
        }

        $user = auth()->user();
        $followers = $user->followers()->get();
        Notification::send($followers, new UserCreatePostNotification($user));

        SendUserCreatePostNotificationEvent::dispatch();

        return ApiResponse::sendResponse(201, 'Post created successfully', new PostResource($post));
    }

    public function update(UpdatePostRequest $request)
    {
        $post = Post::where('slug', $request->slug)->first();

        if ($request->user()->id != $post->user->id) {
            return ApiResponse::sendResponse(401, 'Unauthorized', []);
        }


        $data = [
            'content' => $request->content,
        ];

        if ($request->hasFile('photo')) {
            $photo = $request->file('photo');

            $photoName = Str::slug($request->content) . '-' . time() . '.' . $photo->getClientOriginalExtension();

            $photo->storeAs('public/posts/images', $photoName);

            $data['photo'] = Storage::url($photoName);
        }
        if ($request->hasFile('video')) {
            $video = $request->file('video');

            $videoName = Str::slug($request->content) . '-' . time() . '.' . $video->getClientOriginalExtension();

            $video->storeAs('public/posts/videos', $videoName);

            $data['video'] = Storage::url($videoName);
        }

        if (!empty($request->tags)) {
            $tags = explode(' ', $request->tags);
            $normalizedTags = array_map(function ($tag) {
                return strtolower(ltrim($tag, '#'));
            }, $tags);
            return $normalizedTags;
            $post->syncTags($normalizedTags);
        } else {
            $tags = $post->tags;
            $post->detachTags($tags);
        }

        $post->update($data);

        return ApiResponse::sendResponse(200, 'Post Updated successfully', new PostResource($post));
    }

    public function delete(Request $request)
    {
        $post = Post::where('slug', $request->slug)->first();

        if ($request->user()->id != $post->user->id) {
            return ApiResponse::sendResponse(401, 'Unauthorized', []);
        }

        if ($post) {
            $post->delete();
            return ApiResponse::sendResponse(200, 'Post deleted successfully', []);
        }
        return ApiResponse::sendResponse(404, 'Post not found', []);
    }

    public function search(Request $request)
    {
        if ($request->search) {
            $searchQuery = $request->search;

            if (str_starts_with($searchQuery, '#')) {
                $hashtag = ltrim($searchQuery, '#');
                $posts = Post::withAnyTags([$hashtag])->paginate(5);
            } else {
                $posts = Post::where('content', 'like', '%' . $request->search . '%')->paginate(5);
            }
        }




        if (count($posts) > 0) {
            if ($posts->total() > $posts->perPage()) {
                $data = [
                    'data' => PostResource::collection($posts),
                    'pagination links' => [
                        'current page' => $posts->currentPage(),
                        'per page' => $posts->perPage(),
                        'total' => $posts->total(),
                        'links' => [
                            'first page' => $posts->url(1),
                            'next page' => $posts->nextPageUrl(),
                            'prev page' => $posts->previousPageUrl(),
                            'last page' => $posts->url($posts->lastPage()),
                        ],
                    ],
                ];
            } else {
                $data = PostResource::collection($posts);
            }
            return ApiResponse::sendResponse(200, 'Posts retrieved successfully', $data);
        }
        return ApiResponse::sendResponse(404, 'No posts found', []);
    }

    public function favorite(Request $request)
    {

        $post = Post::where('slug', $request->slug)->first();

        $user = auth()->user();

        $user->favorites()->create([
            'user_id' => $user->id,
            'post_id' => $post->id,
        ]);

        return ApiResponse::sendResponse(200, 'Post added to favorite successfully', new PostResource($post));
    }

    public function getFavoritePosts(Request $request)
    {

        $user = auth()->user();

        if ($request->has('email') && $request->email === $user->email) {
            $favorites = $user->favorites;
            $posts = Post::whereIn('id', $favorites->pluck('post_id'))->get();

            return ApiResponse::sendResponse(
                200,
                'Favorite posts retrieved successfully',
                PostResource::collection($posts)
            );
        }
    }
}
