<?php

use Illuminate\Http\Request;
use App\Http\Controllers\Api\AuthenticationAPisController;
use Illuminate\Support\Facades\Route;
// use App\Http\Controllers\AuthenticationAPisController;



Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::post('/register', [AuthenticationAPisController::class, 'register']);
// Route::get('/delete/{id}', [AuthenticationAPisController::class, 'delete']);
Route::get('/getAllUsers', [AuthenticationAPisController::class, 'getAllUsers']);

// Route::get('/logout', [AuthenticationAPisController::class, 'logout']);

Route::post('/login', [AuthenticationAPisController::class, 'login']);

Route::middleware('auth:sanctum')->get('/getUserData', [AuthenticationAPisController::class, 'getUserData']);

Route::middleware('auth:sanctum')->post('/logout', [AuthenticationAPisController::class, 'logout']);

Route::middleware('auth:sanctum')->post('/update', [AuthenticationAPisController::class, 'update']);

Route::middleware('auth:sanctum')->post('/deleteUser', [AuthenticationAPisController::class, 'deleteUser']);


// Route::get('/delete/{id}', [AuthenticationAPisController::class, 'delete']);


Route::middleware('auth:sanctum')->post('/changePassword', [AuthenticationAPisController::class, 'changepassword']);

// Route::post('', 'UserController@changePassword');


Route::post('/forgotPassword', [AuthenticationAPisController::class, 'forgotPassword']);

Route::post('/resetPasswordWithOTP', [AuthenticationAPisController::class, 'resetPasswordWithOTP']);

Route::post('/verifyOTP', [AuthenticationAPisController::class, 'verifyOTP']);




// Route::middleware('auth:api')->group(function () {
//     Route::get('/userlogin', [AuthenticationAPisController::class, 'userdd']);
// }); 
