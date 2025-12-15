<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserAuthController;
use App\Http\Controllers\Message\MessageController;
use App\Http\Controllers\Api\DestinationController;
use App\Http\Controllers\Api\AccommodationController;
use App\Http\Controllers\Api\VehicleController;


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

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/message/send', [MessageController::class, 'send']);
    Route::get('/message/user/{id}', [MessageController::class, 'getByUser']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/destinations', [DestinationController::class, 'index']);
    Route::get('/destinations/{id}', [DestinationController::class, 'show']);
    Route::post('/destinations', [DestinationController::class, 'store']);
    Route::put('/destinations/{id}', [DestinationController::class, 'update']);
    Route::delete('/destinations/{id}', [DestinationController::class, 'destroy']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/accommodations', [AccommodationController::class, 'index']);
    Route::get('/accommodations/{id}', [AccommodationController::class, 'show']);
    Route::post('/accommodations', [AccommodationController::class, 'store']);
    Route::put('/accommodations/{id}', [AccommodationController::class, 'update']);
    Route::delete('/accommodations/{id}', [AccommodationController::class, 'destroy']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/vehicles', [VehicleController::class, 'index']);
    Route::get('/vehicles/{id}', [VehicleController::class, 'show']);
    Route::post('/vehicles', [VehicleController::class, 'store']);
    Route::put('/vehicles/{id}', [VehicleController::class, 'update']);
    Route::delete('/vehicles/{id}', [VehicleController::class, 'destroy']);
});
