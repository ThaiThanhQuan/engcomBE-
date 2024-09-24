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

    Route::resource('notebook', NotebookController::class);
    
    Route::resource('blog', BlogController::class);

    Route::post('changeRole', [AuthController::class, 'updateRole']);
});
Route::group(['prefix' => 'admin'], function () {
    Route::resource('user', UserController::class);
});

   