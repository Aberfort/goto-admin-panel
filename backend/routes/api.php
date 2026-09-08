<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

// Відкриті маршрути для логіну та реєстрації
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Захищені маршрути
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
});
