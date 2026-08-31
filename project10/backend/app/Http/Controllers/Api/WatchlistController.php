<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Watchlist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WatchlistController extends Controller
{
    public function index(): JsonResponse
    {
        $userId = 1;

        $items = Watchlist::with('video')
            ->where('user_id', $userId)
            ->latest()
            ->get();

        return response()->json([
            'data' => $items,
        ], 200);
    }

    public function store(Request $request): JsonResponse
    {
        $userId = 1;

        $validated = $request->validate([
            'video_id' => ['required', 'integer', 'exists:videos,id'],
        ]);

        $watchlist = Watchlist::firstOrCreate([
            'user_id' => $userId,
            'video_id' => $validated['video_id'],
        ]);

        return response()->json([
            'message' => 'Video added to watchlist',
            'data' => $watchlist,
        ], 201);
    }

    public function destroy(int $videoId): JsonResponse
    {
        $userId = 1;

        Watchlist::where('user_id', $userId)
            ->where('video_id', $videoId)
            ->delete();

        return response()->json([
            'message' => 'Video removed from watchlist',
        ], 200);
    }
}
