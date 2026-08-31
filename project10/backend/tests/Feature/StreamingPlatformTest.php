<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Video;
use App\Models\WatchHistory;
use App\Models\Watchlist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StreamingPlatformTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_and_list_videos(): void
    {
        $response = $this->postJson('/api/78706/v1/videos', [
            'title' => 'Interstellar',
            'description' => 'Space movie',
            'genre' => 'Sci-Fi',
            'duration_minutes' => 169,
            'thumbnail_url' => 'https://example.com/thumb.jpg',
            'video_url' => 'https://example.com/video.mp4',
            'rating' => 9.2,
            'album_number' => '78706',
        ]);

        $response->assertStatus(201)
                 ->assertJsonPath('data.title', 'Interstellar')
                 ->assertJsonPath('data.rating', 9.2);

        $this->assertDatabaseHas('videos', [
            'title' => 'Interstellar',
            'album_number' => '78706',
        ]);

        $listResponse = $this->getJson('/api/78706/v1/videos?genre=Sci-Fi');
        $listResponse->assertStatus(200)
                     ->assertJsonCount(1, 'data');
    }

    public function test_can_show_single_video(): void
    {
        $video = Video::create([
            'title' => 'Dune',
            'genre' => 'Sci-Fi',
            'duration_minutes' => 155,
            'album_number' => '78706',
        ]);

        $response = $this->getJson('/api/78706/v1/videos/' . $video->id);
        $response->assertStatus(200)
                 ->assertJsonPath('data.title', 'Dune');
    }

    public function test_can_save_watch_history_and_retrieve_continue_watching(): void
    {
        User::factory()->create(['id' => 1]);
        $video = Video::create([
            'title' => 'Inception',
            'genre' => 'Sci-Fi',
            'duration_minutes' => 148,
            'album_number' => '78706',
        ]);

        $response = $this->postJson('/api/78706/v1/watch-history', [
            'video_id' => $video->id,
            'progress_seconds' => 1200,
            'completed' => false,
        ]);

        $response->assertStatus(200)
                 ->assertJsonPath('data.progress_seconds', 1200);

        $continueResponse = $this->getJson('/api/78706/v1/continue-watching');
        $continueResponse->assertStatus(200)
                         ->assertJsonCount(1, 'data')
                         ->assertJsonPath('data.0.video.title', 'Inception');
    }

    public function test_recommendations_based_on_watched_genres(): void
    {
        User::factory()->create(['id' => 1]);

        $video1 = Video::create([
            'title' => 'Watched Sci-Fi',
            'genre' => 'Sci-Fi',
            'duration_minutes' => 120,
            'rating' => 8.0,
            'album_number' => '78706',
        ]);

        $video2 = Video::create([
            'title' => 'Recommended Sci-Fi',
            'genre' => 'Sci-Fi',
            'duration_minutes' => 130,
            'rating' => 9.5,
            'album_number' => '78706',
        ]);

        $video3 = Video::create([
            'title' => 'Other Comedy',
            'genre' => 'Comedy',
            'duration_minutes' => 90,
            'rating' => 7.0,
            'album_number' => '78706',
        ]);

        WatchHistory::create([
            'user_id' => 1,
            'video_id' => $video1->id,
            'progress_seconds' => 300,
            'completed' => false,
            'watched_at' => now(),
        ]);

        $response = $this->getJson('/api/78706/v1/recommendations');
        $response->assertStatus(200)
                 ->assertJsonPath('data.0.title', 'Recommended Sci-Fi');
    }

    public function test_watchlist_add_list_and_remove(): void
    {
        User::factory()->create(['id' => 1]);
        $video = Video::create([
            'title' => 'Watchlist Video',
            'genre' => 'Action',
            'duration_minutes' => 110,
            'album_number' => '78706',
        ]);

        // Add
        $addResponse = $this->postJson('/api/78706/v1/watchlist', [
            'video_id' => $video->id,
        ]);
        $addResponse->assertStatus(201)
                    ->assertJson(['message' => 'Video added to watchlist']);

        // List
        $listResponse = $this->getJson('/api/78706/v1/watchlist');
        $listResponse->assertStatus(200)
                     ->assertJsonCount(1, 'data');

        // Delete
        $deleteResponse = $this->deleteJson('/api/78706/v1/watchlist/' . $video->id);
        $deleteResponse->assertStatus(200)
                       ->assertJson(['message' => 'Video removed from watchlist']);
    }
}
