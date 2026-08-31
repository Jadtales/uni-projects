<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class RestaurantController extends Controller
{
    public function index(): JsonResponse
    {
        $restaurants = Restaurant::query()
            ->latest()
            ->get();

        return response()->json([
            'data' => $restaurants,
        ], 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'category' => ['nullable', 'string', 'max:100'],
            'rating' => ['nullable', 'numeric', 'between:0,5'],
            'album_number' => ['required', 'string', 'max:50'],
        ]);

        $restaurant = Restaurant::create($validated);

        Cache::flush();

        return response()->json([
            'data' => $restaurant,
        ], 201);
    }

    public function show(string|int $id): JsonResponse
    {
        $restaurant = Restaurant::find($id);

        if (!$restaurant) {
            return response()->json(['message' => 'Restaurant not found'], 404);
        }

        return response()->json([
            'data' => $restaurant,
        ], 200);
    }

    public function update(Request $request, string|int $id): JsonResponse
    {
        $restaurant = Restaurant::find($id);

        if (!$restaurant) {
            return response()->json(['message' => 'Restaurant not found'], 404);
        }

        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'latitude' => ['sometimes', 'required', 'numeric', 'between:-90,90'],
            'longitude' => ['sometimes', 'required', 'numeric', 'between:-180,180'],
            'category' => ['nullable', 'string', 'max:100'],
            'rating' => ['nullable', 'numeric', 'between:0,5'],
            'album_number' => ['sometimes', 'required', 'string', 'max:50'],
        ]);

        $restaurant->update($validated);

        Cache::flush();

        return response()->json([
            'data' => $restaurant,
        ], 200);
    }

    public function destroy(string|int $id): JsonResponse
    {
        $restaurant = Restaurant::find($id);

        if (!$restaurant) {
            return response()->json(['message' => 'Restaurant not found'], 404);
        }

        $restaurant->delete();

        Cache::flush();

        return response()->json(null, 204);
    }

    public function nearby(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
            'radius' => ['required', 'numeric', 'min:0.1', 'max:100'],
        ]);

        $lat = (float) $validated['lat'];
        $lng = (float) $validated['lng'];
        $radius = (float) $validated['radius'];

        $cacheKey = "nearby:{$lat}:{$lng}:{$radius}";

        $restaurants = Cache::remember($cacheKey, 60, function () use ($lat, $lng, $radius) {
            return Restaurant::query()
                ->get()
                ->map(function (Restaurant $restaurant) use ($lat, $lng) {
                    $restaurant->distance_km = $this->calculateDistance(
                        $lat,
                        $lng,
                        (float) $restaurant->latitude,
                        (float) $restaurant->longitude
                    );

                    return $restaurant;
                })
                ->filter(function (Restaurant $restaurant) use ($radius) {
                    return $restaurant->distance_km <= $radius;
                })
                ->sortBy('distance_km')
                ->values();
        });

        return response()->json([
            'query' => [
                'latitude' => $lat,
                'longitude' => $lng,
                'radius_km' => $radius,
            ],
            'count' => $restaurants->count(),
            'data' => $restaurants,
        ], 200);
    }

    private function calculateDistance(
        float $lat1,
        float $lng1,
        float $lat2,
        float $lng2
    ): float {
        $earthRadiusKm = 6371;

        $lat1Rad = deg2rad($lat1);
        $lng1Rad = deg2rad($lng1);
        $lat2Rad = deg2rad($lat2);
        $lng2Rad = deg2rad($lng2);

        $latDiff = $lat2Rad - $lat1Rad;
        $lngDiff = $lng2Rad - $lng1Rad;

        $a = sin($latDiff / 2) ** 2
            + cos($lat1Rad) * cos($lat2Rad) * sin($lngDiff / 2) ** 2;

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return round($earthRadiusKm * $c, 3);
    }
}
