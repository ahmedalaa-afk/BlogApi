<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Resources\PostResource;
use App\Http\Resources\ProfileResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password as RulesPassword;

class ProfileController extends Controller
{
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|exists:users,email',
        ], [], [
            'email' => 'Email'
        ]);

        if ($validator->fails()) {
            return ApiResponse::sendResponse(422, $validator->errors()->messages(), []);
        }

        $user = User::where('email', $request->email)->first();

        if ($user) {
            return ApiResponse::sendResponse(200, 'user profile retrieved successfully', new ProfileResource($user));
        }
    }

    public function updateEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|exists:users,email',
            'new_email' => 'required|email',
        ], [], [
            'email' => 'Email',
            'new_email' => 'New Email',
        ]);

        if ($validator->fails()) {
            return ApiResponse::sendResponse(422, $validator->errors()->messages(), []);
        }

        if ($request->user()->id == auth()->user()->id) {
            $user = auth()->user();
            $user->email = $request->new_email;
            $user->save();
            return ApiResponse::sendResponse(200, 'user email updated successfully', new ProfileResource($user));
        }
    }
    public function updatePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|exists:users,email',
            'old_password' => 'required',
            'new_password' => 'required|confirmed|min:8',
        ], [], [
            'email' => 'Email',
            'old_password' => 'Old Password',
            'new_password' => 'New Password',
            'password_confirmation' => 'Confirm Password',
        ]);

        if ($validator->fails()) {
            return ApiResponse::sendResponse(422, $validator->errors()->messages(), []);
        }

        if ($request->user()->id == auth()->user()->id) {
            $user = auth()->user();

            if (Hash::check($request->old_password, $user->password)) {
                $user->password = Hash::make($request->new_password);
                $user->save();

                return ApiResponse::sendResponse(200, 'User password updated successfully', new ProfileResource($user));
            } else {
                return ApiResponse::sendResponse(401, 'Invalid old password', []);
            }
        }
    }
    public function updateName(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|exists:users,email',
            'name' => 'required',
        ], [], [
            'email' => 'Email',
            'N=name' => 'Name',
        ]);

        if ($validator->fails()) {
            return ApiResponse::sendResponse(422, $validator->errors()->messages(), []);
        }

        if ($request->user()->id == auth()->user()->id) {
            $user = auth()->user();

            $user->name = $request->name;
            $user->save();
            return ApiResponse::sendResponse(200, 'User name updated successfully', new ProfileResource($user));
        }
    }

    public function userPosts(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|exists:users,email',
        ], [], [
            'email' => 'Email',
        ]);

        if ($validator->fails()) {
            return ApiResponse::sendResponse(422, $validator->errors()->messages(), []);
        }

        $user = User::where('email', $request->email)->first();

        if ($user->email == auth()->user()->email) {
            $posts = $user->posts;
            return ApiResponse::sendResponse(200, 'user posts retrieved successfully', PostResource::collection($posts));
        }
        return ApiResponse::sendResponse(401, 'Unauthorized', []);
    }

    public function search(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string',
        ], [], [
            'name' => 'Name',
        ]);

        if ($validator->fails()) {
            return ApiResponse::sendResponse(422, $validator->errors()->messages(), []);
        }
        if (!empty($request->name)) {
            $users = User::where('name', 'like', '%' . $request->name . '%')->get();
            return ApiResponse::sendResponse(200, 'users retrieved successfully', ProfileResource::collection($users));
        }
        return ApiResponse::sendResponse(200, 'Please enter a name', []);
    }
}
