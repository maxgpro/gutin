<?php

use App\Http\Controllers\HhAuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| HeadHunter Integration Routes
|--------------------------------------------------------------------------
|
| Роуты для интеграции с HeadHunter API. Доступны только пользователям
| с ролью 'mentee' или администраторам.
|
*/

// Callback должен быть доступен без middleware, так как пользователь может быть не залогинен
Route::get('/hh/oauth/callback', [HhAuthController::class, 'callback'])->name('hh.callback');

Route::middleware(['hh.access'])->group(function () {
    Route::get('/hh/oauth/redirect', [HhAuthController::class, 'redirect'])->name('hh.redirect');
    Route::post('/hh/oauth/disconnect', [HhAuthController::class, 'disconnect'])
        ->middleware('hh.auth')
        ->name('hh.disconnect');
});