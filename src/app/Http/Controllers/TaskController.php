<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateTaskRequest;
use App\Http\Requests\UpdateTaskStatusRequest;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TaskController extends Controller
{
    // ─────────────────────────────────────────────────────────────────────────
    // GET /api/tasks
    // List tasks sorted by priority (high→low) then due_date ascending.
    // Optional ?status= filter.
    // ─────────────────────────────────────────────────────────────────────────
    public function index(Request $request): JsonResponse
    {
        $query = Task::query();

        // Optional status filter
        if ($request->filled('status')) {
            $status = $request->input('status');

            // Validate the filter value
            if (!in_array($status, ['pending', 'in_progress', 'done'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid status filter. Use: pending, in_progress, done.',
                ], 422);
            }

            $query->where('status', $status);
        }

        /*
         * Priority sort: PostgreSQL CASE expression maps enum values to
         * integers so we can ORDER BY priority weight descending.
         */
        $tasks = $query
            ->orderByRaw("
                CASE priority
                    WHEN 'high'   THEN 3
                    WHEN 'medium' THEN 2
                    WHEN 'low'    THEN 1
                    ELSE 0
                END DESC
            ")
            ->orderBy('due_date', 'asc')
            ->get();

        if ($tasks->isEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'No tasks found.',
                'data'    => [],
            ], 200);
        }

        return response()->json([
            'success' => true,
            'message' => 'Tasks retrieved successfully.',
            'count'   => $tasks->count(),
            'data'    => $tasks,
        ], 200);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // POST /api/tasks
    // Create a new task.
    // Business rule: title cannot duplicate a task with the same due_date.
    // ─────────────────────────────────────────────────────────────────────────
    public function store(CreateTaskRequest $request): JsonResponse
    {
        // Business rule: unique title per due_date
        $duplicate = Task::where('title', $request->title)
            ->where('due_date', $request->due_date)
            ->exists();

        if ($duplicate) {
            return response()->json([
                'success' => false,
                'message' => 'A task with this title already exists for the given due date.',
                'errors'  => [
                    'title' => ['Title must be unique for the selected due date.'],
                ],
            ], 422);
        }

        $task = Task::create([
            'title'    => $request->title,
            'due_date' => $request->due_date,
            'priority' => $request->priority,
            'status'   => 'pending', // always starts as pending
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Task created successfully.',
            'data'    => $task,
        ], 201);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PATCH /api/tasks/{id}/status
    // Update task status — must follow: pending → in_progress → done
    // Cannot skip or revert.
    // ─────────────────────────────────────────────────────────────────────────
    public function updateStatus(UpdateTaskStatusRequest $request, int $id): JsonResponse
    {
        $task = Task::find($id);

        if (!$task) {
            return response()->json([
                'success' => false,
                'message' => "Task with ID {$id} not found.",
            ], 404);
        }

        $newStatus = $request->input('status');

        // Already at the requested status
        if ($task->status === $newStatus) {
            return response()->json([
                'success' => false,
                'message' => "Task is already in '{$newStatus}' status.",
            ], 422);
        }

        // Enforce linear progression
        if (!$task->canTransitionTo($newStatus)) {
            $current = $task->status;
            $allowed = $task->nextStatus();

            $hint = $allowed
                ? "Current status is '{$current}'. The only allowed next status is '{$allowed}'."
                : "Task is already at final status 'done' and cannot be updated further.";

            return response()->json([
                'success' => false,
                'message' => 'Invalid status transition. ' . $hint,
            ], 422);
        }

        $task->update(['status' => $newStatus]);

        return response()->json([
            'success' => true,
            'message' => "Task status updated to '{$newStatus}' successfully.",
            'data'    => $task->fresh(),
        ], 200);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // DELETE /api/tasks/{id}
    // Only tasks with status = 'done' can be deleted.
    // ─────────────────────────────────────────────────────────────────────────
    public function destroy(int $id): JsonResponse
    {
        $task = Task::find($id);

        if (!$task) {
            return response()->json([
                'success' => false,
                'message' => "Task with ID {$id} not found.",
            ], 404);
        }

        if ($task->status !== 'done') {
            return response()->json([
                'success' => false,
                'message' => 'Only completed (done) tasks can be deleted.',
                'hint'    => "Current status is '{$task->status}'. Progress the task to 'done' before deleting.",
            ], 403);
        }

        $task->delete();

        return response()->json([
            'success' => true,
            'message' => 'Task deleted successfully.',
        ], 200);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // GET /api/tasks/report?date=YYYY-MM-DD
    // BONUS: Daily report — counts per priority and status for a given date.
    // ─────────────────────────────────────────────────────────────────────────
    public function report(Request $request): JsonResponse
    {
        // Validate date param
        $request->validate([
            'date' => 'required|date_format:Y-m-d',
        ]);

        $date = $request->input('date');

        // Fetch counts grouped by priority + status for the given due_date
        $rows = Task::where('due_date', $date)
            ->select('priority', 'status', DB::raw('count(*) as total'))
            ->groupBy('priority', 'status')
            ->get();

        // Build the summary scaffold with all zeros
        $priorities = ['high', 'medium', 'low'];
        $statuses   = ['pending', 'in_progress', 'done'];

        $summary = [];
        foreach ($priorities as $p) {
            foreach ($statuses as $s) {
                $summary[$p][$s] = 0;
            }
        }

        // Fill in actual counts
        foreach ($rows as $row) {
            $summary[$row->priority][$row->status] = (int) $row->total;
        }

        return response()->json([
            'success' => true,
            'date'    => $date,
            'summary' => $summary,
        ], 200);
    }
}