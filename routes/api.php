<?php
namespace App\Http\Controllers;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\NotebookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::group([
    'middleware' => 'api',
    'prefix' => 'engcom'
],function ($router) {
    Route::post('register', [AuthController::class, 'register']); 
    Route::post('login', [AuthController::class, 'login']);
    Route::get('profile', [AuthController::class, 'profile']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::post('social', [AuthController::class, 'getSocialUser']);
    Route::post('notebook/store', [NotebookController::class, 'store']);
    Route::post('notebook/show', [NotebookController::class, 'show']);
    Route::post('notebook/update', [NotebookController::class, 'update']);
    Route::post('notebook/destroy', [NotebookController::class, 'destroy']);
});