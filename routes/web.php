<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\HhAuthController;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/hh/oauth/redirect', [HhAuthController::class, 'redirect'])->name('hh.redirect');
Route::get('/hh/oauth/callback', [HhAuthController::class, 'callback'])->name('hh.callback');

Route::post('/hh/oauth/disconnect', [HhAuthController::class, 'disconnect'])
    ->middleware(['auth', 'hh.auth'])
    ->name('hh.disconnect');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
