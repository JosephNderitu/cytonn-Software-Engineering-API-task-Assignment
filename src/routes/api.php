<?php

use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Task Management API Routes
|--------------------------------------------------------------------------
|
| All routes are prefixed with /api automatically by Laravel's bootstrap.
| We register the bonus report route BEFORE the resource-style routes so
| Laravel does not treat "report" as a task {id}.
|
*/

// ─── Bonus: Daily Report ─────────────────────────────────────────────────────
Route::get('/tasks/report', [TaskController::class, 'report']);

// ─── Core Task Endpoints ──────────────────────────────────────────────────────
Route::get('/tasks',              [TaskController::class, 'index']);
Route::post('/tasks',             [TaskController::class, 'store']);
Route::patch('/tasks/{id}/status',[TaskController::class, 'updateStatus']);
Route::delete('/tasks/{id}',      [TaskController::class, 'destroy']);