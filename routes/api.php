<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

Route::post('/admin/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/admin/logout', [AuthController::class, 'logout']);
    Route::get('/admin/me', [AuthController::class, 'me']);
});
