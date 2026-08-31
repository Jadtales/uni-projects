<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Follow;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class FollowController extends Controller
{
    public function follow(int $id): JsonResponse
    {
        $followerId = 1;

        if ($followerId === $id) {
            return response()->json([
                'message' => 'You cannot follow yourself',
            ], 422);
        }

        $exists = Follow::query()
            ->where('follower_id', $followerId)
            ->where('followed_id', $id)
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Already following',
            ], 409);
        }

        Follow::create([
            'follower_id' => $followerId,
            'followed_id' => $id,
        ]);

        Cache::flush();

        return response()->json([
            'message' => 'Followed successfully',
        ], 201);
    }

    public function unfollow(int $id): JsonResponse
    {
        $followerId = 1;

        Follow::query()
            ->where('follower_id', $followerId)
            ->where('followed_id', $id)
            ->delete();

        Cache::flush();

        return response()->json([
            'message' => 'Unfollowed successfully',
        ], 200);
    }
}
