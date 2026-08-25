<?php

use App\Http\Controllers\AuthController;
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

    // Video Learning with Word Module
    Route::get('/video-learning-words', [VideoLearningWordController::class, 'index'])->name('video-learning.index');
    Route::get('/video-learning-words/create', [VideoLearningWordController::class, 'create'])->name('video-learning.create');
    Route::post('/video-learning-words', [VideoLearningWordController::class, 'store'])->name('video-learning.store');
    Route::get('/video-learning-words/{id}', [VideoLearningWordController::class, 'show'])->name('video-learning.show');
    Route::get('/video-learning-words/{id}/edit', [VideoLearningWordController::class, 'edit'])->name('video-learning.edit');
    Route::post('/video-learning-words/{id}/update', [VideoLearningWordController::class, 'update'])->name('video-learning.update');
    Route::delete('/video-learning-words/{id}', [VideoLearningWordController::class, 'destroy'])->name('video-learning.destroy');
    Route::post('/video-learning-words/reorder', [VideoLearningWordController::class, 'reorder'])->name('video-learning.reorder');
    Route::patch('/video-learning-words/{id}/toggle-visibility', [VideoLearningWordController::class, 'toggleVisibility'])->name('video-learning.toggle-visibility');

    // API List & Documentation Module
    Route::get('/api-list', [ApiListController::class, 'index'])->name('api-list.index');
});

