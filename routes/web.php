<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\VideoLearningWordController;
use App\Http\Controllers\LearnupWordController;
use App\Http\Controllers\LearnupCategoryController;
use App\Http\Controllers\ApiListController;
use App\Http\Controllers\SecureMediaController;
use App\Http\Middleware\ProtectAdminMediaUrls;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

Route::middleware(['auth', ProtectAdminMediaUrls::class])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Secure media: streams a token's image file (no real path in the page).
    Route::get('/secure-media', [SecureMediaController::class, 'stream'])->name('secure-media');

    // Secure video stream: resolves the file from the DB id (POST, no URL exposed).
    Route::post('/video-learning-words/{id}/stream', [VideoLearningWordController::class, 'stream'])->whereNumber('id')->name('video-learning.stream');

    // Category Module
    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::get('/categories/create', [CategoryController::class, 'create'])->name('categories.create');
    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::post('/categories/reorder', [CategoryController::class, 'reorder'])->name('categories.reorder');
    Route::get('/categories/{id}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
    Route::post('/categories/{id}/update', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{id}', [CategoryController::class, 'destroy'])->name('categories.destroy');
    Route::patch('/categories/{id}/toggle-status', [CategoryController::class, 'toggleStatus'])->name('categories.toggle-status');

    // Video Learning with Word Module
    Route::get('/video-learning-words', [VideoLearningWordController::class, 'index'])->name('video-learning.index');
    Route::get('/video-learning-words/create', [VideoLearningWordController::class, 'create'])->name('video-learning.create');
    Route::get('/video-learning-words/category/{categoryId}/videos', [VideoLearningWordController::class, 'categoryVideos'])->whereNumber('categoryId')->name('video-learning.category-videos');
    Route::post('/video-learning-words', [VideoLearningWordController::class, 'store'])->name('video-learning.store');
    Route::get('/video-learning-words/{id}', [VideoLearningWordController::class, 'show'])->name('video-learning.show');
    Route::get('/video-learning-words/{id}/edit', [VideoLearningWordController::class, 'edit'])->name('video-learning.edit');
    Route::post('/video-learning-words/{id}/update', [VideoLearningWordController::class, 'update'])->name('video-learning.update');
    Route::delete('/video-learning-words/{id}', [VideoLearningWordController::class, 'destroy'])->name('video-learning.destroy');
    Route::post('/video-learning-words/reorder', [VideoLearningWordController::class, 'reorder'])->name('video-learning.reorder');
    Route::patch('/video-learning-words/{id}/toggle-visibility', [VideoLearningWordController::class, 'toggleVisibility'])->name('video-learning.toggle-visibility');
    Route::get('/video-learning-words-list', [VideoLearningWordController::class, 'listAjax'])->name('video-learning.list-ajax');

    // ──────────────── LearnUp with AI Module (independent clone) ────────────────
    Route::post('/learnup-words/{id}/stream', [LearnupWordController::class, 'stream'])->whereNumber('id')->name('learnup.stream');

    // LearnUp Categories
    Route::get('/learnup-categories', [LearnupCategoryController::class, 'index'])->name('learnup-categories.index');
    Route::get('/learnup-categories/create', [LearnupCategoryController::class, 'create'])->name('learnup-categories.create');
    Route::post('/learnup-categories', [LearnupCategoryController::class, 'store'])->name('learnup-categories.store');
    Route::post('/learnup-categories/reorder', [LearnupCategoryController::class, 'reorder'])->name('learnup-categories.reorder');
    Route::get('/learnup-categories/{id}/edit', [LearnupCategoryController::class, 'edit'])->name('learnup-categories.edit');
    Route::post('/learnup-categories/{id}/update', [LearnupCategoryController::class, 'update'])->name('learnup-categories.update');
    Route::delete('/learnup-categories/{id}', [LearnupCategoryController::class, 'destroy'])->name('learnup-categories.destroy');
    Route::patch('/learnup-categories/{id}/toggle-status', [LearnupCategoryController::class, 'toggleStatus'])->name('learnup-categories.toggle-status');

    // LearnUp content
    Route::get('/learnup-words', [LearnupWordController::class, 'index'])->name('learnup.index');
    Route::get('/learnup-words/create', [LearnupWordController::class, 'create'])->name('learnup.create');
    Route::get('/learnup-words/category/{categoryId}/videos', [LearnupWordController::class, 'categoryVideos'])->whereNumber('categoryId')->name('learnup.category-videos');
    Route::post('/learnup-words', [LearnupWordController::class, 'store'])->name('learnup.store');
    Route::get('/learnup-words/{id}', [LearnupWordController::class, 'show'])->name('learnup.show');
    Route::get('/learnup-words/{id}/edit', [LearnupWordController::class, 'edit'])->name('learnup.edit');
    Route::post('/learnup-words/{id}/update', [LearnupWordController::class, 'update'])->name('learnup.update');
    Route::delete('/learnup-words/{id}', [LearnupWordController::class, 'destroy'])->name('learnup.destroy');
    Route::post('/learnup-words/reorder', [LearnupWordController::class, 'reorder'])->name('learnup.reorder');
    Route::patch('/learnup-words/{id}/toggle-visibility', [LearnupWordController::class, 'toggleVisibility'])->name('learnup.toggle-visibility');
    Route::get('/learnup-words-list', [LearnupWordController::class, 'listAjax'])->name('learnup.list-ajax');

    // API List & Documentation (per module)
    Route::get('/api-list', [ApiListController::class, 'index'])->name('api-list.index');
    Route::get('/learnup-api-list', [ApiListController::class, 'learnup'])->name('api-list.learnup');
});

