<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    // return Inertia::render('Welcome');
    return redirect()->route('blog.posts.index');
})->name('home');

Route::get('/i18n-demo', function () {
    return Inertia::render('I18nDemo');
})->name('i18n.demo');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Locale switching - unified endpoint for both AJAX and form requests
Route::post('locale/switch', [\App\Http\Controllers\LocaleController::class, 'switch'])
    ->middleware([\App\Http\Middleware\LocaleThrottleMiddleware::class])
    ->name('locale.switch');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
require __DIR__.'/blog.php';
