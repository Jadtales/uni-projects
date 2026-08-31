<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Support\ApiError;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class TaskController extends Controller
{
    /**
     * Display a listing of all tasks (cached for 60 seconds).
     */
    public function index(): JsonResponse
    {
        $tasks = Cache::remember('tasks.index', 60, function () {
            return Task::all();
        });

        return response()->json($tasks, 200);
    }

    /**
     * Store a newly created task in storage and invalidate cache.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'nullable|string',
            'album_number' => 'required|string',
        ]);

        if (!isset($validated['status'])) {
            $validated['status'] = 'todo';
        }

        $task = Task::create($validated);

        Cache::forget('tasks.index');

        return response()->json($task, 201);
    }

    /**
     * Display the specified task or return standardized 404 error.
     */
    public function show(Request $request, string|int $id): JsonResponse
    {
        $task = Task::find($id);

        if (!$task) {
            return ApiError::make($request, 404, 'Task not found');
        }

        return response()->json($task, 200);
    }

    /**
     * Update the specified task in storage and invalidate cache.
     */
    public function update(Request $request, string|int $id): JsonResponse
    {
        $task = Task::find($id);

        if (!$task) {
            return ApiError::make($request, 404, 'Task not found');
        }

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|nullable|string',
            'status' => 'sometimes|string',
            'album_number' => 'sometimes|string',
        ]);

        $task->update($validated);

        Cache::forget('tasks.index');

        return response()->json($task, 200);
    }

    /**
     * Remove the specified task from storage and invalidate cache.
     */
    public function destroy(Request $request, string|int $id): JsonResponse
    {
        $task = Task::find($id);

        if (!$task) {
            return ApiError::make($request, 404, 'Task not found');
        }

        $task->delete();

        Cache::forget('tasks.index');

        return response()->json(null, 204);
    }

    /**
     * Get a summary overview of task counts.
     */
    public function summary(): JsonResponse
    {
        return response()->json([
            'total_tasks' => Task::count(),
            'todo' => Task::where('status', 'todo')->count(),
            'in_progress' => Task::where('status', 'in_progress')->count(),
            'done' => Task::where('status', 'done')->count(),
            'timestamp' => now()->toDateTimeString(),
        ], 200);
    }
}
