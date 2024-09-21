<?php
namespace App\Http\Controllers;
use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::group([
    'middleware' => 'api',
    'prefix' => 'auth'
<<<<<<< HEAD
],function ($router) {

    Route::post('login', [AuthController::class,'login']);
    // Route::post('logout', 'AuthController@logout');
    // Route::post('refresh', 'AuthController@refresh');
    // Route::get('profile', 'AuthController@me');

=======
>>>>>>> 5b328da2190fd53fef6a8c5e052b817c5e71649e
});