<?php

namespace Database\Seeders;

use App\Models\Restaurant;
use Illuminate\Database\Seeder;

class RestaurantSeeder extends Seeder
{
    public function run(): void
    {
        Restaurant::query()->delete();

        Restaurant::insert([
            // Katowice
            [
                'name' => 'Katowice Pizza House',
                'latitude' => 50.2649000,
                'longitude' => 19.0238000,
                'category' => 'pizza',
                'rating' => 4.5,
                'album_number' => '78706',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Sushi Katowice Center',
                'latitude' => 50.2599000,
                'longitude' => 19.0216000,
                'category' => 'sushi',
                'rating' => 4.7,
                'album_number' => '78706',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Vegan Bistro Katowice',
                'latitude' => 50.2701000,
                'longitude' => 19.0302000,
                'category' => 'vegan',
                'rating' => 4.3,
                'album_number' => '78706',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Burger Point Katowice',
                'latitude' => 50.2518000,
                'longitude' => 19.0154000,
                'category' => 'burger',
                'rating' => 4.1,
                'album_number' => '78706',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Warsaw
            [
                'name' => 'Warsaw Pizza Market',
                'latitude' => 52.2297000,
                'longitude' => 21.0122000,
                'category' => 'pizza',
                'rating' => 4.4,
                'album_number' => '78706',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Warsaw Vegan Garden',
                'latitude' => 52.2370000,
                'longitude' => 21.0175000,
                'category' => 'vegan',
                'rating' => 4.6,
                'album_number' => '78706',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Krakow
            [
                'name' => 'Krakow Sushi Bar',
                'latitude' => 50.0647000,
                'longitude' => 19.9450000,
                'category' => 'sushi',
                'rating' => 4.8,
                'album_number' => '78706',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Krakow Burger Street',
                'latitude' => 50.0619000,
                'longitude' => 19.9368000,
                'category' => 'burger',
                'rating' => 4.2,
                'album_number' => '78706',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Gdansk
            [
                'name' => 'Gdansk Sea Pizza',
                'latitude' => 54.3520000,
                'longitude' => 18.6466000,
                'category' => 'pizza',
                'rating' => 4.0,
                'album_number' => '78706',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Gdansk Sushi Dock',
                'latitude' => 54.3563000,
                'longitude' => 18.6520000,
                'category' => 'sushi',
                'rating' => 4.5,
                'album_number' => '78706',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
