<?php

namespace Tests\Feature;

use App\Models\Restaurant;
use Database\Seeders\RestaurantSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RestaurantTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_restaurants(): void
    {
        $this->seed(RestaurantSeeder::class);

        $response = $this->getJson('/api/78706/v1/restaurants');

        $response->assertStatus(200)
                 ->assertJsonCount(10, 'data')
                 ->assertJsonFragment([
                     'name' => 'Katowice Pizza House',
                     'album_number' => '78706',
                 ]);
    }

    public function test_can_create_restaurant(): void
    {
        $response = $this->postJson('/api/78706/v1/restaurants', [
            'name' => 'Test Restaurant',
            'latitude' => 50.2649,
            'longitude' => 19.0238,
            'category' => 'pizza',
            'rating' => 4.6,
            'album_number' => '78706',
        ]);

        $response->assertStatus(201)
                 ->assertJsonFragment([
                     'name' => 'Test Restaurant',
                     'album_number' => '78706',
                 ]);

        $this->assertDatabaseHas('restaurants', [
            'name' => 'Test Restaurant',
            'album_number' => '78706',
        ]);
    }

    public function test_can_show_restaurant(): void
    {
        $restaurant = Restaurant::create([
            'name' => 'Single Restaurant',
            'latitude' => 50.2649,
            'longitude' => 19.0238,
            'album_number' => '78706',
        ]);

        $response = $this->getJson('/api/78706/v1/restaurants/' . $restaurant->id);

        $response->assertStatus(200)
                 ->assertJsonFragment([
                     'name' => 'Single Restaurant',
                 ]);
    }

    public function test_can_update_restaurant(): void
    {
        $restaurant = Restaurant::create([
            'name' => 'Old Name',
            'latitude' => 50.2649,
            'longitude' => 19.0238,
            'album_number' => '78706',
        ]);

        $response = $this->putJson('/api/78706/v1/restaurants/' . $restaurant->id, [
            'name' => 'New Name',
        ]);

        $response->assertStatus(200)
                 ->assertJsonFragment([
                     'name' => 'New Name',
                 ]);
    }

    public function test_can_delete_restaurant(): void
    {
        $restaurant = Restaurant::create([
            'name' => 'To Delete',
            'latitude' => 50.2649,
            'longitude' => 19.0238,
            'album_number' => '78706',
        ]);

        $response = $this->deleteJson('/api/78706/v1/restaurants/' . $restaurant->id);

        $response->assertStatus(204);

        $this->assertDatabaseMissing('restaurants', [
            'id' => $restaurant->id,
        ]);
    }

    public function test_nearby_search_filters_by_radius_and_sorts_by_distance(): void
    {
        $this->seed(RestaurantSeeder::class);

        // Search near Katowice center (lat: 50.2649, lng: 19.0238, radius: 10km)
        $response = $this->getJson('/api/78706/v1/restaurants/nearby?lat=50.2649&lng=19.0238&radius=10');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'query' => [
                         'latitude',
                         'longitude',
                         'radius_km',
                     ],
                     'count',
                     'data',
                 ]);

        $data = $response->json('data');

        // All 4 Katowice restaurants should be within 10km
        $this->assertCount(4, $data);

        // First result should be closest (Katowice Pizza House at distance 0 km)
        $this->assertEquals('Katowice Pizza House', $data[0]['name']);
        $this->assertEquals(0, $data[0]['distance_km']);
    }

    public function test_nearby_validation_errors(): void
    {
        $response = $this->getJson('/api/78706/v1/restaurants/nearby?lat=invalid');

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['lat', 'lng', 'radius']);
    }
}
