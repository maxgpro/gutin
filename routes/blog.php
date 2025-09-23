<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BlogPostController;
use App\Http\Controllers\BlogCategoryController;

// Public blog routes
Route::prefix('blog')->name('blog.')->group(function () {
    // Public post viewing
    Route::get('/', [BlogPostController::class, 'index'])->name('posts.index');
    Route::get('/posts/{post}', [BlogPostController::class, 'show'])->name('posts.show');
    
    // Category viewing
    Route::get('/categories', [BlogCategoryController::class, 'index'])->name('categories.index');
    Route::get('/categories/{category}', [BlogCategoryController::class, 'show'])->name('categories.show');
});

// Admin/Author blog routes (protected)
Route::middleware(['auth'])->prefix('admin/blog')->name('blog.')->group(function () {
    // Post management
    Route::get('/posts/create', [BlogPostController::class, 'create'])->name('posts.create');
    Route::post('/posts', [BlogPostController::class, 'store'])->name('posts.store');
    Route::get('/posts/{post}/edit', [BlogPostController::class, 'edit'])->name('posts.edit');
    Route::put('/posts/{post}', [BlogPostController::class, 'update'])->name('posts.update');
    Route::delete('/posts/{post}', [BlogPostController::class, 'destroy'])->name('posts.destroy');

    // Category management
    Route::get('/categories/create', [BlogCategoryController::class, 'create'])->name('categories.create');
    Route::post('/categories', [BlogCategoryController::class, 'store'])->name('categories.store');
    Route::get('/categories/{category}/edit', [BlogCategoryController::class, 'edit'])->name('categories.edit');
    Route::put('/categories/{category}', [BlogCategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{category}', [BlogCategoryController::class, 'destroy'])->name('categories.destroy');
});