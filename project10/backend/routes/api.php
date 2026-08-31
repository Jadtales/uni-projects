<?php

use App\Http\Controllers\Api\FeedController;
use App\Http\Controllers\Api\FollowController;
use App\Http\Controllers\Api\PhotoController;
use App\Http\Controllers\Api\RecommendationController;
use App\Http\Controllers\Api\RestaurantController;
use App\Http\Controllers\Api\ShortLinkController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\VideoController;
use App\Http\Controllers\Api\WatchHistoryController;
use App\Http\Controllers\Api\WatchlistController;
use App\Http\Controllers\EchoController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/health', function () {
    return response()->json(['status' => 'ok'], 200);
});

Route::get('/echo', [EchoController::class, 'echo']);
Route::post('/echo', [EchoController::class, 'echo']);
Route::get('/echo/ping', [EchoController::class, 'ping']);

// Versioned API routes for Student ID 78706
Route::prefix('78706/v1')->group(function () {
    Route::apiResource('tasks', TaskController::class);

    Route::apiResource('short-links', ShortLinkController::class)
        ->only(['index', 'store', 'show']);

    Route::get('restaurants/nearby', [RestaurantController::class, 'nearby']);
    Route::apiResource('restaurants', RestaurantController::class);

    Route::apiResource('photos', PhotoController::class)
        ->only(['index', 'store', 'show', 'destroy']);

    Route::post('users/{id}/follow', [FollowController::class, 'follow']);
    Route::delete('users/{id}/follow', [FollowController::class, 'unfollow']);
    Route::get('feed', [FeedController::class, 'index']);

    // Project 10 routes
    Route::get('videos', [VideoController::class, 'index']);
    Route::post('videos', [VideoController::class, 'store']);
    Route::get('videos/{video}', [VideoController::class, 'show']);
    Route::post('watch-history', [WatchHistoryController::class, 'store']);
    Route::get('continue-watching', [WatchHistoryController::class, 'continueWatching']);
    Route::get('recommendations', [RecommendationController::class, 'index']);
    Route::get('watchlist', [WatchlistController::class, 'index']);
    Route::post('watchlist', [WatchlistController::class, 'store']);
    Route::delete('watchlist/{videoId}', [WatchlistController::class, 'destroy']);
});

// Non-versioned fallback routes
Route::apiResource('photos', PhotoController::class)->only(['index', 'store', 'show', 'destroy']);
Route::get('/restaurants/nearby', [RestaurantController::class, 'nearby']);
Route::apiResource('restaurants', RestaurantController::class);
Route::get('/tasks/summary', [TaskController::class, 'summary']);
Route::get('/tasks', [TaskController::class, 'index']);
Route::post('/tasks', [TaskController::class, 'store']);
Route::get('/tasks/{id}', [TaskController::class, 'show']);
Route::put('/tasks/{id}', [TaskController::class, 'update']);
Route::delete('/tasks/{id}', [TaskController::class, 'destroy']);
