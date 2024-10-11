<?php

namespace App\Http\Controllers;

use App\Events\NewFollowerNotificationEvent;
use App\Events\UserUnfollowNotificationEvent;
use App\Helpers\ApiResponse;
use App\Http\Resources\FollowerResource;
use App\Models\Follower;
use App\Models\User;
use App\Notifications\NewFollowerNotification;
use App\Notifications\UserUnfollowNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;

class FollowController extends Controller
{
    public function followers(Request $request)
    {
        $user = auth()->user();
        $followers = $user->followers;
        if (count($followers) > 0) {
            return ApiResponse::sendResponse(200, 'followers retrieved', FollowerResource::collection($followers));
        }
        return ApiResponse::sendResponse(200, 'No followers found', []);
    }
    public function followings(Request $request)
    {
        $user = auth()->user();
        $followings = $user->followings;
        if (count($followings) > 0) {
            return ApiResponse::sendResponse(200, 'followings retrieved', FollowerResource::collection($followings));
        }
        return ApiResponse::sendResponse(200, 'No followings found', []);
    }

    public function follow(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'email' => 'required|exists:users,email',
            ],
            [],
            [
                'email' => 'Email'
            ]
        );

        if ($validator->fails()) {
            return ApiResponse::sendResponse(400, 'Validation Error', []);
        }
        if ($request->email == auth()->user()->email) {
            return ApiResponse::sendResponse(400, 'Cannot follow yourself', []);
        }
        $user = User::where('email', $request->email)->first();

        $follower = Auth::user();

        if ($follower->followings->contains('email', $user->email)) {
            return ApiResponse::sendResponse(200, 'you already following this user', []);
        }

        Follower::create([
            'follower_id' => $follower->id,
            'user_id' => $user->id,
        ]);

        Notification::send($user, new NewFollowerNotification($follower));
        NewFollowerNotificationEvent::dispatch();

        return ApiResponse::sendResponse(200, 'Followed Successfully', new FollowerResource($user));
    }
    public function unfollow(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'email' => 'required|exists:users,email',
            ],
            [],
            [
                'email' => 'Email'
            ]
        );

        if ($validator->fails()) {
            return ApiResponse::sendResponse(400, 'Validation Error', []);
        }
        if ($request->email == auth()->user()->email) {
            return ApiResponse::sendResponse(400, 'Cannot unfollow yourself', []);
        }

        $follower = Auth::user();
        $user = User::where('email', $request->email)->first();

        if (!$follower->followings->contains('email', $user->email)) {
            return ApiResponse::sendResponse(200, 'you already not following this user', []);
        }

        Follower::where('user_id', $user->id)->where('follower_id', $follower->id)->first()->delete();

        Notification::send($user, new UserUnfollowNotification($follower));
        UserUnfollowNotificationEvent::dispatch();

        return ApiResponse::sendResponse(200, 'Unfollowed Successfully', []);
    }
}
