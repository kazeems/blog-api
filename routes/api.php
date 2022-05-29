<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\PostsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/posts', [PostsController::class, 'showPosts']);
Route::get('posts/{post}', [PostsController::class, 'showPost']);

Route::get('/categories', [CategoryController::class, 'getCategories']);

Route::group(['middleware' => 'auth:sanctum'], function(){
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('/users', [AuthController::class, 'getUsers']);
    Route::post('/users/password/update', [AuthController::class, 'updatePassword']);

    // Category
    Route::post('/categories/create', [CategoryController::class, 'createCategory']);

    // Posts authenticated routes
    Route::post('/posts', [PostsController::class, 'createPost']);
    Route::put('posts/{post}', [PostsController::class, 'updatePost']);
    Route::delete('posts/{post}', [PostsController::class, 'deletePost']);

    Route::post('posts/{post}/comment', [PostsController::class, 'createComment']);
    Route::post('posts/{post}/comment/{comment}/reply', [PostsController::class, 'createComment']);
});
