<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\WebsiteController;
use Illuminate\Support\Facades\Route;

// Відкриті маршрути для логіну та реєстрації
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Захищені маршрути
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
});

// Публічна перевірка сайту за токеном (використовується зовнішніми сайтами)
Route::get('/websites/token/{api_token}', [WebsiteController::class, 'showByToken']);

// CRUD для вебсайтів вимагає автентифікації
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('websites', WebsiteController::class);
    Route::post('/websites/{id}/regenerate-token', [WebsiteController::class, 'regenerateToken']);
});
