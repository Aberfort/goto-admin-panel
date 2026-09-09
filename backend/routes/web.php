<?php

use App\Http\Controllers\QrCodeController;
use App\Http\Controllers\RedirectController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json(['name' => config('app.name'), 'status' => 'ok']);
});

// A real browser navigation returning an HTTP redirect - not a JSON API
// call, so this lives outside the api/ prefix and its JSON-oriented
// exception handling.
Route::get('/r/{code}', [RedirectController::class, 'go'])->name('links.redirect');
Route::post('/r/{code}', [RedirectController::class, 'unlock'])->name('links.unlock');

// Public so the QR can be used directly as an <img src>. Throttled
// because, unlike the redirect, each hit does real rendering work.
Route::get('/qr/{code}.svg', [QrCodeController::class, 'show'])
    ->middleware('throttle:60,1')
    ->name('links.qr');

// Custom domains: <verified host>/{code}. Registered last so it can't
// shadow anything above - on the app's own host these one-segment paths
// find no matching domain and 404, exactly as before.
Route::get('/{code}', [RedirectController::class, 'goOnDomain'])
    ->where('code', '[A-Za-z0-9_-]+');
Route::post('/{code}', [RedirectController::class, 'unlockOnDomain'])
    ->where('code', '[A-Za-z0-9_-]+');
