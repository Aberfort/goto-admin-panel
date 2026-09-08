<?php

use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ConfigController;
use App\Http\Controllers\LinkController;
use App\Http\Controllers\SiteController;
use Illuminate\Support\Facades\Route;

// Публічні маршрути
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/config', [ConfigController::class, 'index']);

// Захищені маршрути
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    Route::apiResource('sites', SiteController::class);
    Route::apiResource('sites.links', LinkController::class)->shallow();
    Route::patch('/links/{link}/toggle', [LinkController::class, 'toggle']);

    Route::get('/sites/{site}/analytics', [AnalyticsController::class, 'site']);
    Route::get('/links/{link}/analytics', [AnalyticsController::class, 'link']);
});
