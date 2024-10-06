<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\profile;
use App\Models\User;
use App\Notifications\ResetPasswordVerficationNotification;
use Ichtrojan\Otp\Otp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'name' => ['required', 'string'],
                'email' => ['required', 'email', 'unique:users,email'], // Assuming 'users' is the table name
                'password' => ['required', 'confirmed', Password::defaults()],
            ],
            [],
            [
                'name' => 'Name',
                'email' => 'Email',
                'password' => 'Password',
                'password_confirmation' => 'Confirm Password',
            ]
        );


        if ($validator->fails()) {
            return ApiResponse::sendResponse(422, 'Validation Error', $validator->errors()->messages());
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        profile::create(['user_id' => $user->id]);

        $data = [
            'name' => $user->name,
            'email' => $user->email,
            'token' => $user->createToken('registerUserToken')->plainTextToken
        ];
        return ApiResponse::sendResponse(201, 'User registered successfully', $data);
    }

    public function login(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'email' => ['required', 'email'],
                'password' => ['required'],
            ],
            [],
            [
                'email' => 'Email',
                'password' => 'Password',
            ]
        );

        if ($validator->fails()) {
            return ApiResponse::sendResponse(422, 'Validation Error', $validator->errors()->messages());
        }

        if (Auth::attempt($request->only(['email', 'password']))) {
            $user = $request->user();
            $data = [
                'name' => $user->name,
                'email' => $user->email,
                'token' => $user->createToken('loginUserToken')->plainTextToken
            ];
            return ApiResponse::sendResponse(200, 'User logged in successfully', $data);
        }
        return ApiResponse::sendResponse(401, 'Invalid credentials', []);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return ApiResponse::sendResponse(200, 'User Logged out successfully', []);
    }

    public function forgetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email', 'exists:users,email'],
        ], [], [
            'email' => 'Email',
        ]);

        if ($validator->fails()) {
            return ApiResponse::sendResponse(422, 'Validation Error', $validator->errors()->messages());
        }

        $user = User::where('email', $request->email)->first();
        $user->notify(new ResetPasswordVerficationNotification());
        return ApiResponse::sendResponse(200, 'Password reset link sent successfully', []);
    }
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email', 'exists:users,email'],
            'otp' => ['required', 'max:6'],
            'password' => ['required', 'string', 'min:8', 'confirmed']
        ], [], [
            'email' => 'Email',
            'otp' => 'OTP',
            'password' => 'Password',
            'password_confirmation' => 'Confirm Password',
        ]);

        if ($validator->fails()) {
            return ApiResponse::sendResponse(422, 'Validation Error', $validator->errors()->messages());
        }
        $otp = new Otp();
        $validation = $otp->validate($request->email, $request->otp);

        if (!$validation->status) {
            return ApiResponse::sendResponse(401, 'Invalid OTP', []);
        }
        $user = User::where('email', $request->email)->first();
        $user->update([
            'password' =>  bcrypt($request->password),
        ]);
        $token = $user->currentAccessToken();

        if ($token) {
            $token->delete();
        }


        return ApiResponse::sendResponse(200, 'Password reset successfully', []);
    }
}
