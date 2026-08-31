<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WatchHistory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class WatchHistoryController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $userId = 1;

        $validated = $request->validate([
            'video_id' => ['required', 'integer', 'exists:videos,id'],
            'progress_seconds' => ['required', 'integer', 'min:0'],
            'completed' => ['nullable', 'boolean'],
        ]);

        $watchHistory = WatchHistory::updateOrCreate(
            [
                'user_id' => $userId,
                'video_id' => $validated['video_id'],
            ],
            [
                'progress_seconds' => $validated['progress_seconds'],
                'completed' => $validated['completed'] ?? false,
                'watched_at' => now(),
            ]
        );

        Cache::flush();

        return response()->json([
            'data' => $watchHistory,
        ], 200);
    }

    public function continueWatching(): JsonResponse
    {
        $userId = 1;

        $items = Cache::remember("continue_watching:{$userId}", 60, function () use ($userId) {
            return WatchHistory::with('video')
                ->where('user_id', $userId)
                ->where('completed', false)
                ->where('progress_seconds', '>', 0)
                ->latest('watched_at')
                ->get();
        });

        return response()->json([
            'data' => $items,
        ], 200);
    }
}
