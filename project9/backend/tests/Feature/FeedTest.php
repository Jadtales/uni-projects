<?php

namespace Tests\Feature;

use App\Models\Follow;
use App\Models\Photo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FeedTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_follow_user(): void
    {
        // Create user 1 (the follower) and user 2 (the target)
        User::factory()->create(['id' => 1]);
        $targetUser = User::factory()->create(['id' => 2]);

        $response = $this->postJson('/api/78706/v1/users/' . $targetUser->id . '/follow');

        $response->assertStatus(201)
                 ->assertJson(['message' => 'Followed successfully']);

        $this->assertDatabaseHas('follows', [
            'follower_id' => 1,
            'followed_id' => 2,
        ]);
    }

    public function test_cannot_follow_self(): void
    {
        $response = $this->postJson('/api/78706/v1/users/1/follow');

        $response->assertStatus(422)
                 ->assertJson(['message' => 'You cannot follow yourself']);
    }

    public function test_cannot_follow_same_user_twice(): void
    {
        User::factory()->create(['id' => 1]);
        $targetUser = User::factory()->create(['id' => 2]);

        Follow::create([
            'follower_id' => 1,
            'followed_id' => 2,
        ]);

        $response = $this->postJson('/api/78706/v1/users/' . $targetUser->id . '/follow');

        $response->assertStatus(409)
                 ->assertJson(['message' => 'Already following']);
    }

    public function test_can_unfollow_user(): void
    {
        User::factory()->create(['id' => 1]);
        $targetUser = User::factory()->create(['id' => 2]);

        Follow::create([
            'follower_id' => 1,
            'followed_id' => 2,
        ]);

        $response = $this->deleteJson('/api/78706/v1/users/' . $targetUser->id . '/follow');

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Unfollowed successfully']);

        $this->assertDatabaseMissing('follows', [
            'follower_id' => 1,
            'followed_id' => 2,
        ]);
    }

    public function test_feed_is_empty_when_not_following_anyone(): void
    {
        $response = $this->getJson('/api/78706/v1/feed');

        $response->assertStatus(200)
                 ->assertJson([
                     'count' => 0,
                     'data' => [],
                     'next_cursor' => null,
                     'prev_cursor' => null,
                 ]);
    }

    public function test_feed_returns_photos_from_followed_users_only(): void
    {
        User::factory()->create(['id' => 1]);
        $followedUser = User::factory()->create(['id' => 2]);
        $unfollowedUser = User::factory()->create(['id' => 3]);

        Follow::create([
            'follower_id' => 1,
            'followed_id' => 2,
        ]);

        Photo::create([
            'user_id' => 2,
            'title' => 'Followed Photo 1',
            'image_path' => 'photos/f1.jpg',
            'original_filename' => 'f1.jpg',
            'mime_type' => 'image/jpeg',
            'file_size' => 1024,
            'processing_status' => 'processed',
            'album_number' => '78706',
        ]);

        Photo::create([
            'user_id' => 3,
            'title' => 'Unfollowed Photo',
            'image_path' => 'photos/u1.jpg',
            'original_filename' => 'u1.jpg',
            'mime_type' => 'image/jpeg',
            'file_size' => 1024,
            'processing_status' => 'processed',
            'album_number' => '78706',
        ]);

        $response = $this->getJson('/api/78706/v1/feed');

        $response->assertStatus(200)
                 ->assertJsonCount(1, 'data')
                 ->assertJsonPath('data.0.title', 'Followed Photo 1');
    }
}
