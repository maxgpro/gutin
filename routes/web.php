<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    // return Inertia::render('Welcome');
    return redirect()->route('blog.posts.index');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Locale switching
Route::post('locale/{locale}', [\App\Http\Controllers\LocaleController::class, 'switch'])
    ->name('locale.switch');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
require __DIR__.'/blog.php';
require __DIR__.'/hh.php';
