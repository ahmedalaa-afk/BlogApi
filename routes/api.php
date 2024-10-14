<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\EmojiController;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\FollwerController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProfileController;
use App\Models\Emoji;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });


Route::prefix('auth')->controller(AuthController::class)->group(function () {
    Route::post('register', 'register');
    Route::post('login', 'login');
    Route::post('logout', 'logout')->middleware('auth:sanctum');
    Route::post('forgetPassword', 'forgetPassword');
    Route::post('resetPassword', 'resetPassword');
});

Route::prefix('user')->middleware('auth:sanctum')->group(function () {
    // Profile Section
    Route::prefix('profile')->controller(ProfileController::class)->group(function () {
        Route::get('/', 'index');
        Route::post('/updateName', 'updateName');
        Route::post('/updateEmail', 'updateEmail');
        Route::post('/updatePassword', 'updatePassword');
        Route::get('/userPosts', 'userPosts');
        Route::get('/search', 'search');
    });
    // Follow Section
    Route::controller(FollowController::class)->group(function (){
        Route::get('/followers', 'followers');
        Route::get('/followings', 'followings');
        Route::post('/follow', 'follow');
        Route::post('/unfollow', 'unfollow');
    });
    // Post Section
    Route::prefix('posts')->controller(PostController::class)->group(function () {
        Route::get('/', 'index');
        Route::post('/store', 'store');
        Route::post('/update', 'update');
        Route::delete('/delete', 'delete');
        Route::get('/search', 'search');
        Route::post('/favorite', 'favorite');
        Route::get('/getFavoritePosts', 'getFavoritePosts');
        // Comment Section
        Route::prefix('comments')->controller(CommentController::class)->group(function () {
            Route::post('/store', 'store');
            Route::delete('/delete', 'delete');
        });

        // Emoji Section
        Route::prefix('emoji')->controller(EmojiController::class)->group(function () {
            Route::post('/store', 'store');
        });
    });
});
