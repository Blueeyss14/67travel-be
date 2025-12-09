<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserAuthController;

use Illuminate\Support\Facades\Route;

Route::post('/admin/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/admin/logout', [AuthController::class, 'logout']);
    Route::get('/admin/me', [AuthController::class, 'me']);
});


Route::post('/user/register', [UserAuthController::class, 'register']);
Route::post('/user/login', [UserAuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/user/logout', [UserAuthController::class, 'logout']);
    Route::get('/user/me', [UserAuthController::class, 'me']);

    Route::get('/user/all', [UserAuthController::class, 'getAllUser']);
    Route::put('/user/update/{id}', [UserAuthController::class, 'update']);
    Route::delete('/user/delete/{id}', [UserAuthController::class, 'delete']);
});
