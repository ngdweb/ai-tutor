<?php

use App\Http\Controllers\Api\VideoLearningApiController;
use App\Http\Middleware\VerifyApiBearerToken;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Protected by Bearer Token Authentication Middleware
|
*/

Route::middleware([VerifyApiBearerToken::class])->group(function () {
    Route::get('/video-learning-words', [VideoLearningApiController::class, 'index'])->name('api.video-learning.index');
    Route::get('/video-learning-words/{id}', [VideoLearningApiController::class, 'show'])->name('api.video-learning.show');
    Route::get('/video-learning', [VideoLearningApiController::class, 'index'])->name('api.video-learning.alias');
});
