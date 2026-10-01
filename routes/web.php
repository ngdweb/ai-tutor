<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\VideoLearningWordController;
use App\Http\Controllers\ApiListController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

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

    // API List & Documentation Module
    Route::get('/api-list', [ApiListController::class, 'index'])->name('api-list.index');
});

