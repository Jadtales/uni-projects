<?php

namespace Tests\Feature;

use App\Models\ShortLink;
use App\Support\Base62;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShortLinkTest extends TestCase
{
    use RefreshDatabase;

    public function test_base62_encoder(): void
    {
        $this->assertEquals('0', Base62::encode(0));
        $this->assertEquals('1', Base62::encode(1));
        $this->assertEquals('Z', Base62::encode(61));
        $this->assertEquals('10', Base62::encode(62));
    }

    public function test_can_create_short_link(): void
    {
        $response = $this->postJson('/api/78706/v1/short-links', [
            'original_url' => 'https://laravel.com/docs/routing',
            'album_number' => '78706',
        ]);

        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'data' => [
                         'id',
                         'original_url',
                         'short_code',
                         'album_number',
                     ],
                     'short_url',
                 ]);

        $this->assertDatabaseHas('short_links', [
            'original_url' => 'https://laravel.com/docs/routing',
            'album_number' => '78706',
        ]);
    }

    public function test_validation_rejects_invalid_url(): void
    {
        $response = $this->postJson('/api/78706/v1/short-links', [
            'original_url' => 'not-a-valid-url',
            'album_number' => '78706',
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['original_url']);
    }

    public function test_validation_rejects_missing_album_number(): void
    {
        $response = $this->postJson('/api/78706/v1/short-links', [
            'original_url' => 'https://example.com',
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['album_number']);
    }

    public function test_can_list_short_links(): void
    {
        $link = ShortLink::create([
            'original_url' => 'https://example.com/page1',
            'album_number' => '78706',
        ]);
        $link->short_code = Base62::encode($link->id);
        $link->save();

        $response = $this->getJson('/api/78706/v1/short-links');

        $response->assertStatus(200)
                 ->assertJsonFragment([
                     'original_url' => 'https://example.com/page1',
                     'short_code' => $link->short_code,
                 ]);
    }

    public function test_redirect_to_original_url_and_increments_clicks(): void
    {
        $link = ShortLink::create([
            'original_url' => 'https://laravel.com/docs/routing',
            'album_number' => '78706',
        ]);
        $link->short_code = '1';
        $link->save();

        $response = $this->get('/r/1');

        $response->assertStatus(302)
                 ->assertRedirect('https://laravel.com/docs/routing');

        $this->assertDatabaseHas('short_links', [
            'id' => $link->id,
            'click_count' => 1,
        ]);
    }

    public function test_redirect_returns_404_for_unknown_code(): void
    {
        $response = $this->get('/r/doesnotexist');

        $response->assertStatus(404);
    }
}
