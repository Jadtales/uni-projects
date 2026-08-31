<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Video;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class VideoController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Video::query();

        if ($request->filled('genre')) {
            $query->where('genre', $request->query('genre'));
        }

        if ($request->query('sort_by') === 'rating') {
            $order = $request->query('order', 'desc');
            $query->orderBy('rating', $order);
        } else {
            $query->latest();
        }

        $perPage = (int) $request->query('per_page', 10);
        $videos = $query->paginate($perPage);

        return response()->json($videos, 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'genre' => ['required', 'string', 'max:100'],
            'duration_minutes' => ['required', 'integer', 'min:1'],
            'thumbnail_url' => ['nullable', 'url', 'max:2048'],
            'video_url' => ['nullable', 'url', 'max:2048'],
            'rating' => ['nullable', 'numeric', 'between:0,10'],
            'album_number' => ['required', 'string', 'max:50'],
        ]);

        $video = Video::create($validated);

        Cache::flush();

        return response()->json([
            'data' => $video,
        ], 201);
    }

    public function show(string|int $id): JsonResponse
    {
        $video = Video::find($id);

        if (!$video) {
            return response()->json(['message' => 'Video not found'], 404);
        }

        return response()->json([
            'data' => $video,
        ], 200);
    }
}
