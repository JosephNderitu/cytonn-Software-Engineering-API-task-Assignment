<?php

use App\Http\Controllers\TaskController;
use App\Http\Controllers\DatabaseController;
use Illuminate\Support\Facades\Route;

//  Daily Report (must be before {id} routes) ────────────────────────
Route::get('/tasks/report', [TaskController::class, 'report']);

// ─── Core Task Endpoints ──────────────────────────────────────────────────────
Route::get('/tasks',               [TaskController::class, 'index']);
Route::post('/tasks',              [TaskController::class, 'store']);
Route::patch('/tasks/{id}/status', [TaskController::class, 'updateStatus']);
Route::delete('/tasks/{id}',       [TaskController::class, 'destroy']);

// ─── Database Viewer Endpoints ────────────────────────────────────────────────
Route::get('/db/tables',           [DatabaseController::class, 'tables']);
Route::get('/db/tables/{table}',   [DatabaseController::class, 'show']);