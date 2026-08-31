<?php

namespace Tests\Feature;

use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Project5Test extends TestCase
{
    use RefreshDatabase;

    public function test_health_endpoint(): void
    {
        $response = $this->getJson('/api/health');
        $response->assertStatus(200)
                 ->assertJson(['status' => 'ok']);
    }

    public function test_versioned_tasks_list(): void
    {
        Task::create([
            'title' => 'Versioned Task',
            'status' => 'todo',
            'album_number' => '78706',
        ]);

        $response = $this->getJson('/api/78706/v1/tasks');

        $response->assertStatus(200)
                 ->assertJsonFragment([
                     'title' => 'Versioned Task',
                     'album_number' => '78706',
                 ]);
    }

    public function test_versioned_tasks_create(): void
    {
        $response = $this->postJson('/api/78706/v1/tasks', [
            'title' => 'New Versioned Task',
            'description' => 'Test description',
            'album_number' => '78706',
        ]);

        $response->assertStatus(201)
                 ->assertJsonFragment([
                     'title' => 'New Versioned Task',
                     'status' => 'todo',
                     'album_number' => '78706',
                 ]);
    }

    public function test_versioned_missing_task_returns_standardized_error(): void
    {
        $response = $this->getJson('/api/78706/v1/tasks/999999');

        $response->assertStatus(404)
                 ->assertJson([
                     'error' => [
                         'status' => 404,
                         'message' => 'Task not found',
                         'path' => '/api/78706/v1/tasks/999999',
                     ],
                 ]);
    }

    public function test_versioned_task_update(): void
    {
        $task = Task::create([
            'title' => 'Task to Update',
            'status' => 'todo',
            'album_number' => '78706',
        ]);

        $response = $this->putJson('/api/78706/v1/tasks/' . $task->id, [
            'status' => 'done',
        ]);

        $response->assertStatus(200)
                 ->assertJsonFragment([
                     'status' => 'done',
                 ]);
    }

    public function test_versioned_task_delete(): void
    {
        $task = Task::create([
            'title' => 'Task to Delete',
            'album_number' => '78706',
        ]);

        $response = $this->deleteJson('/api/78706/v1/tasks/' . $task->id);

        $response->assertStatus(204);
    }
}
