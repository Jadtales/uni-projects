<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Video;
use App\Models\WatchHistory;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class RecommendationController extends Controller
{
    public function index(): JsonResponse
    {
        $userId = 1;

        $recommendations = Cache::remember("recommendations:{$userId}", 60, function () use ($userId) {
            $watchedGenres = WatchHistory::where('user_id', $userId)
                ->with('video')
                ->get()
                ->pluck('video.genre')
                ->filter()
                ->unique()
                ->values();

            if ($watchedGenres->isEmpty()) {
                return Video::query()
                    ->orderByDesc('rating')
                    ->limit(10)
                    ->get();
            }

            return Video::query()
                ->whereIn('genre', $watchedGenres)
                ->orderByDesc('rating')
                ->limit(10)
                ->get();
        });

        return response()->json([
            'data' => $recommendations,
        ], 200);
    }
}
