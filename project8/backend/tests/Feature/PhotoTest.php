<?php

namespace Tests\Feature;

use App\Models\Photo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PhotoTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_upload_photo(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->create('test_photo.png', 10, 'image/png');

        $response = $this->postJson('/api/78706/v1/photos', [
            'title' => 'Test upload',
            'caption' => 'My first uploaded image',
            'album_number' => '78706',
            'image' => $file,
        ]);

        $response->assertStatus(201)
                 ->assertJsonPath('data.title', 'Test upload')
                 ->assertJsonPath('data.caption', 'My first uploaded image')
                 ->assertJsonPath('data.album_number', '78706')
                 ->assertJsonPath('data.processing_status', 'processed')
                 ->assertJsonStructure([
                     'data' => [
                         'id',
                         'title',
                         'caption',
                         'image_path',
                         'image_url',
                         'original_filename',
                         'mime_type',
                         'file_size',
                         'processing_status',
                         'album_number',
                         'created_at',
                         'updated_at',
                     ],
                 ]);

        $imagePath = $response->json('data.image_path');
        Storage::disk('public')->assertExists($imagePath);

        $this->assertDatabaseHas('photos', [
            'title' => 'Test upload',
            'album_number' => '78706',
            'processing_status' => 'processed',
        ]);
    }

    public function test_can_list_photos(): void
    {
        Photo::create([
            'title' => 'Listed Photo',
            'caption' => 'Listing test',
            'image_path' => 'photos/test.jpg',
            'original_filename' => 'test.jpg',
            'mime_type' => 'image/jpeg',
            'file_size' => 1024,
            'processing_status' => 'processed',
            'album_number' => '78706',
        ]);

        $response = $this->getJson('/api/78706/v1/photos');

        $response->assertStatus(200)
                 ->assertJsonCount(1, 'data')
                 ->assertJsonPath('data.0.title', 'Listed Photo');
    }

    public function test_can_show_single_photo(): void
    {
        $photo = Photo::create([
            'title' => 'Single Photo',
            'caption' => 'Show test',
            'image_path' => 'photos/single.jpg',
            'original_filename' => 'single.jpg',
            'mime_type' => 'image/jpeg',
            'file_size' => 2048,
            'processing_status' => 'processed',
            'album_number' => '78706',
        ]);

        $response = $this->getJson('/api/78706/v1/photos/' . $photo->id);

        $response->assertStatus(200)
                 ->assertJsonPath('data.id', $photo->id)
                 ->assertJsonPath('data.title', 'Single Photo');
    }

    public function test_can_delete_photo_and_remove_from_storage(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->create('delete_me.png', 10, 'image/png');
        $path = $file->store('photos', 'public');

        $photo = Photo::create([
            'title' => 'To Delete',
            'image_path' => $path,
            'original_filename' => 'delete_me.png',
            'mime_type' => 'image/png',
            'file_size' => 512,
            'album_number' => '78706',
        ]);

        Storage::disk('public')->assertExists($path);

        $response = $this->deleteJson('/api/78706/v1/photos/' . $photo->id);

        $response->assertStatus(204);

        $this->assertDatabaseMissing('photos', ['id' => $photo->id]);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_validation_error_when_missing_required_fields(): void
    {
        $response = $this->postJson('/api/78706/v1/photos', []);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['title', 'album_number', 'image']);
    }

    public function test_validation_error_when_file_is_not_an_image(): void
    {
        $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

        $response = $this->postJson('/api/78706/v1/photos', [
            'title' => 'PDF Upload',
            'album_number' => '78706',
            'image' => $file,
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['image']);
    }

    public function test_returns_404_for_nonexistent_photo(): void
    {
        $response = $this->getJson('/api/78706/v1/photos/999999');

        $response->assertStatus(404);
    }
}
