<?php

use App\Http\Controllers\Api\PhotoController;
use App\Http\Controllers\Api\RestaurantController;
use App\Http\Controllers\Api\ShortLinkController;
use App\Http\Controllers\Api\TaskController;
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
    // Photos
    Route::apiResource('photos', PhotoController::class)->only(['index', 'store', 'show', 'destroy']);

    // Restaurants
    Route::get('/restaurants/nearby', [RestaurantController::class, 'nearby']);
    Route::apiResource('restaurants', RestaurantController::class);

    // Tasks & Short Links
    Route::get('/tasks/summary', [TaskController::class, 'summary']);
    Route::apiResource('tasks', TaskController::class);
    Route::apiResource('short-links', ShortLinkController::class)->only(['index', 'store', 'show']);
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
