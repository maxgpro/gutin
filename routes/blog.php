<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BlogPostController;
use App\Http\Controllers\BlogCategoryController;

// All blog routes in one group
Route::prefix('blog')->name('blog.')->group(function () {
    // Protected routes (require auth)
    Route::middleware(['auth'])->group(function () {
        // Post management (author OR admin via policies)
        Route::post('/posts', [BlogPostController::class, 'store'])->name('posts.store');
        // Специфичные маршруты должны быть определены раньше параметризованных
        Route::get('/posts/create', [BlogPostController::class, 'create'])->name('posts.create');
        Route::get('/posts/{post}/edit', [BlogPostController::class, 'edit'])->name('posts.edit');
        Route::put('/posts/{post}', [BlogPostController::class, 'update'])->name('posts.update');
        Route::delete('/posts/{post}', [BlogPostController::class, 'destroy'])->name('posts.destroy');
        
        // Category management (ADMIN ONLY via middleware)
        Route::middleware(['admin'])->group(function () {
            Route::post('/categories', [BlogCategoryController::class, 'store'])->name('categories.store');
            Route::get('/categories/create', [BlogCategoryController::class, 'create'])->name('categories.create');
            Route::get('/categories/{category}/edit', [BlogCategoryController::class, 'edit'])->name('categories.edit');
            Route::put('/categories/{category}', [BlogCategoryController::class, 'update'])->name('categories.update');
            Route::delete('/categories/{category}', [BlogCategoryController::class, 'destroy'])->name('categories.destroy');
        });
    });

    // Public routes (no middleware)
    Route::get('/posts', [BlogPostController::class, 'index'])->name('posts.index');
    Route::get('/categories', [BlogCategoryController::class, 'index'])->name('categories.index');
    // Параметризованные маршруты должны быть ПОСЛЕ специфичных
    Route::get('/posts/{post}', [BlogPostController::class, 'show'])->name('posts.show');
    Route::get('/categories/{category}', [BlogCategoryController::class, 'show'])->name('categories.show');
});
