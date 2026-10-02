<?php

use App\Http\Controllers\Api\CategoryApiController;
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
    // Category APIs
    // GET /api/categories            -> all active categories, each with latest 5 visible videos
    // GET /api/categories/{id}       -> that category's full visible videos ( {id} = 0 returns ALL videos )
    Route::get('/categories', [CategoryApiController::class, 'index'])->name('api.categories.index');
    // POST: category id in the body { "category_id": 5 } (0 = all videos)
    Route::post('/categories/videos', [CategoryApiController::class, 'videos'])->name('api.categories.videos');
    // GET (kept for backward compatibility): id in the URL
    Route::get('/categories/{id}', [CategoryApiController::class, 'show'])->whereNumber('id')->name('api.categories.show');

    // Existing Video Learning APIs (unchanged)
    Route::get('/video-learning-words', [VideoLearningApiController::class, 'index'])->name('api.video-learning.index');
    Route::get('/video-learning-words/{id}', [VideoLearningApiController::class, 'show'])->name('api.video-learning.show');
    Route::get('/video-learning', [VideoLearningApiController::class, 'index'])->name('api.video-learning.alias');
});

// Example GET API without token requirement
Route::get('/test-api', function (Request $request) {
    try {
        // You can add your actual logic here
        $isSuccess = true; // Use this variable to simulate success or failure

        if ($isSuccess) {
            // Success response with status code 200
            return response()->json([
                'status' => true,
                'message' => 'API executed successfully!'
            ], 200);
        } else {
            // Failure response with status code 400 (Bad Request) or other appropriate code
            return response()->json([
                'status' => false,
                'message' => 'API failed. Specific condition not met.'
            ], 400); 
        }

    } catch (\Exception $e) {
        // Exception/server error response with status code 500
        return response()->json([
            'status' => false,
            'message' => 'An unexpected server error occurred.',
            'error' => $e->getMessage()
        ], 500);
    }
});