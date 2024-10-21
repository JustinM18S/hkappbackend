<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\UserController; 
use Illuminate\Http\Request; 

// Get authenticated user details
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Authentication Routes
Route::post('/auth/login', [UserController::class, 'login']);
Route::post('/auth/verify-code', [UserController::class, 'verifyCode']);
Route::post('/auth/forgot-password', [UserController::class, 'forgotPassword']);
Route::post('/auth/reset-password', [UserController::class, 'resetPassword']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/auth/user', [UserController::class, 'user']);
    Route::post('/auth/logout', [UserController::class, 'logout']);
    Route::post('/auth/logout-all', [UserController::class, 'logoutFromAllDevices']);
});
