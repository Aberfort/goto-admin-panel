<?php

use App\Http\Controllers\RedirectController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json(['name' => config('app.name'), 'status' => 'ok']);
});

// A real browser navigation returning an HTTP redirect - not a JSON API
// call, so this lives outside the api/ prefix and its JSON-oriented
// exception handling.
Route::get('/r/{code}', [RedirectController::class, 'go'])->name('links.redirect');
