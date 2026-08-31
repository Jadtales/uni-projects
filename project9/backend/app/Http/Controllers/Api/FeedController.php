<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Follow;
use App\Models\Photo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class FeedController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $userId = 1;

        $limit = (int) $request->query('limit', 10);
        $limit = max(1, min($limit, 50));

        $cursor = (string) $request->query('cursor', 'first');

        $cacheKey = 'feed:' . $userId . ':' . $limit . ':' . $cursor;

        $result = Cache::remember($cacheKey, 60, function () use ($userId, $limit) {
            $followedIds = Follow::query()
                ->where('follower_id', $userId)
                ->pluck('followed_id')
                ->unique()
                ->values();

            if ($followedIds->isEmpty()) {
                return [
                    'count' => 0,
                    'data' => [],
                    'next_cursor' => null,
                    'prev_cursor' => null,
                ];
            }

            $paginator = Photo::query()
                ->whereIn('user_id', $followedIds)
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->cursorPaginate($limit);

            return [
                'count' => count($paginator->items()),
                'data' => $paginator->items(),
                'next_cursor' => optional($paginator->nextCursor())->encode(),
                'prev_cursor' => optional($paginator->previousCursor())->encode(),
            ];
        });

        return response()->json($result, 200);
    }
}
