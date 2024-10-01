<?php
namespace App\Http\Controllers;
use App\Http\Controllers\AuthController;

use App\Http\Controllers\BlogController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\NotebookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::group([
    'middleware' => 'api',
    'prefix' => 'engcom'
], function ($router) {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::get('profile', [AuthController::class, 'profile']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::post('social', [AuthController::class, 'getSocialUser']);

    Route::get('search', [SearchController::class, 'search']);
    // Upload
    // Class cart banner
    Route::post('upload-cart', [UploadController::class, 'cart']);
    Route::post('delete-cart', [UploadController::class, 'deleteCart']);
    Route::post('upload-video', [UploadController::class, 'uploadVideo']);
    Route::post('delete-video', [UploadController::class, 'deleteVideo']);


    Route::resource('notebook', NotebookController::class);
    Route::resource('blog', BlogController::class);
    Route::resource('class', ClassController::class);
    Route::get('class-more/{type}', [ClassController::class, 'moreShow']);
    
    Route::get('own-class/{user_id}', [ClassController::class, 'ownShow']);
    Route::resource('course', CourseController::class);
    Route::get('own-course/{class_id}', [CourseController::class, 'ownShow']);
    Route::resource('lesson', LessonController::class);


    Route::get('blogs-list/{user_id}', [BlogController::class, 'showList']);
    Route::post('changeRole', [AuthController::class, 'updateRole']);
});

Route::group(['prefix' => 'admin'], function () {
    Route::resource('user', UserController::class);
});

   