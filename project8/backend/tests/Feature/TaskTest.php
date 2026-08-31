<?php

namespace Tests\Feature;

use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_tasks(): void
    {
        Task::create([
            'title' => 'Initial Task',
            'description' => 'Test Description',
            'status' => 'todo',
            'album_number' => '78706',
        ]);

        $response = $this->getJson('/api/tasks');

        $response->assertStatus(200)
                 ->assertJsonFragment([
                     'title' => 'Initial Task',
                     'album_number' => '78706',
                 ]);
    }

    public function test_can_create_task(): void
    {
        $payload = [
            'title' => 'Homework Task',
            'description' => 'Laravel CRUD test',
            'status' => 'todo',
            'album_number' => '78706',
        ];

        $response = $this->postJson('/api/tasks', $payload);

        $response->assertStatus(201)
                 ->assertJsonFragment([
                     'title' => 'Homework Task',
                     'album_number' => '78706',
                     'status' => 'todo',
                 ]);

        $this->assertDatabaseHas('tasks', [
            'title' => 'Homework Task',
            'album_number' => '78706',
        ]);
    }

    public function test_validation_error_when_creating_without_required_fields(): void
    {
        $response = $this->postJson('/api/tasks', [
            'description' => 'Missing title and album_number',
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['title', 'album_number']);
    }

    public function test_can_show_single_task(): void
    {
        $task = Task::create([
            'title' => 'Single Task',
            'description' => 'Show test',
            'status' => 'todo',
            'album_number' => '78706',
        ]);

        $response = $this->getJson('/api/tasks/' . $task->id);

        $response->assertStatus(200)
                 ->assertJsonFragment([
                     'id' => $task->id,
                     'title' => 'Single Task',
                 ]);
    }

    public function test_returns_404_for_nonexistent_task(): void
    {
        $response = $this->getJson('/api/tasks/999999');

        $response->assertStatus(404)
                 ->assertJsonPath('error.message', 'Task not found');
    }

    public function test_can_update_task(): void
    {
        $task = Task::create([
            'title' => 'Before Update',
            'description' => 'Old description',
            'status' => 'todo',
            'album_number' => '78706',
        ]);

        $response = $this->putJson('/api/tasks/' . $task->id, [
            'title' => 'After Update',
            'status' => 'in_progress',
        ]);

        $response->assertStatus(200)
                 ->assertJsonFragment([
                     'id' => $task->id,
                     'title' => 'After Update',
                     'status' => 'in_progress',
                 ]);
    }

    public function test_can_delete_task(): void
    {
        $task = Task::create([
            'title' => 'To be deleted',
            'album_number' => '78706',
        ]);

        $response = $this->deleteJson('/api/tasks/' . $task->id);

        $response->assertStatus(204);

        $this->assertDatabaseMissing('tasks', [
            'id' => $task->id,
        ]);
    }

    public function test_can_get_tasks_summary(): void
    {
        Task::create(['title' => 'Task A', 'status' => 'todo', 'album_number' => '78706']);
        Task::create(['title' => 'Task B', 'status' => 'in_progress', 'album_number' => '78706']);
        Task::create(['title' => 'Task C', 'status' => 'done', 'album_number' => '78706']);

        $response = $this->getJson('/api/tasks/summary');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'total_tasks',
                     'todo',
                     'in_progress',
                     'done',
                     'timestamp',
                 ]);
    }
}
