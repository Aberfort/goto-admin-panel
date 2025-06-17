<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\WebsiteController;
use Illuminate\Support\Facades\Route;

// Відкриті маршрути для логіну
Route::post('/login', [AuthController::class, 'login']);

// Захищені маршрути
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
});

Route::apiResource('websites', WebsiteController::class);
Route::post('/websites/{id}/regenerate-token', [WebsiteController::class, 'regenerateToken']);
Route::get('/websites/token/{api_token}', [WebsiteController::class, 'showByToken']);
