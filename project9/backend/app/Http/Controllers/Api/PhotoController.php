<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Photo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class PhotoController extends Controller
{
    public function index(): JsonResponse
    {
        $photos = Cache::remember('photos.index', 60, function () {
            return Photo::query()
                ->latest()
                ->get()
                ->map(function (Photo $photo) {
                    return $this->formatPhoto($photo);
                });
        });

        return response()->json([
            'data' => $photos,
        ], 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'caption' => ['nullable', 'string'],
            'album_number' => ['required', 'string', 'max:50'],
            'image' => ['required', 'image', 'max:10240'],
        ]);

        $uploadedFile = $request->file('image');
        $path = $uploadedFile->store('photos', 'public');

        $photo = Photo::create([
            'title' => $validated['title'],
            'caption' => $validated['caption'] ?? null,
            'image_path' => $path,
            'original_filename' => $uploadedFile->getClientOriginalName(),
            'mime_type' => $uploadedFile->getMimeType(),
            'file_size' => $uploadedFile->getSize(),
            'processing_status' => 'uploaded',
            'album_number' => $validated['album_number'],
        ]);

        // Simulated asynchronous processing
        $photo->processing_status = 'processed';
        $photo->save();

        Cache::forget('photos.index');
        Cache::forget('photos.show.' . $photo->id);

        return response()->json([
            'data' => $this->formatPhoto($photo),
        ], 201);
    }

    public function show(string|int $id): JsonResponse
    {
        $photo = Photo::find($id);

        if (!$photo) {
            return response()->json(['message' => 'Photo not found'], 404);
        }

        $data = Cache::remember('photos.show.' . $photo->id, 60, function () use ($photo) {
            return $this->formatPhoto($photo);
        });

        return response()->json([
            'data' => $data,
        ], 200);
    }

    public function destroy(string|int $id): JsonResponse
    {
        $photo = Photo::find($id);

        if (!$photo) {
            return response()->json(['message' => 'Photo not found'], 404);
        }

        if (Storage::disk('public')->exists($photo->image_path)) {
            Storage::disk('public')->delete($photo->image_path);
        }

        Cache::forget('photos.index');
        Cache::forget('photos.show.' . $photo->id);

        $photo->delete();

        return response()->json(null, 204);
    }

    private function formatPhoto(Photo $photo): array
    {
        return [
            'id' => $photo->id,
            'title' => $photo->title,
            'caption' => $photo->caption,
            'image_path' => $photo->image_path,
            'image_url' => asset('storage/' . $photo->image_path),
            'original_filename' => $photo->original_filename,
            'mime_type' => $photo->mime_type,
            'file_size' => $photo->file_size,
            'processing_status' => $photo->processing_status,
            'album_number' => $photo->album_number,
            'created_at' => $photo->created_at,
            'updated_at' => $photo->updated_at,
        ];
    }
}
