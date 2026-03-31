<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Creates the tasks table with all required columns and constraints.
     */
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            // Primary key
            $table->id();

            // Task title — indexed for fast duplicate-check queries
            $table->string('title', 255);

            // Deadline — must be today or later (enforced at app layer)
            $table->date('due_date');

            // Priority enum
            $table->enum('priority', ['low', 'medium', 'high'])
                  ->default('medium');

            // Status enum — starts at pending, progresses linearly
            $table->enum('status', ['pending', 'in_progress', 'done'])
                  ->default('pending');

            // Timestamps (created_at, updated_at)
            $table->timestamps();

            // ─── Indexes ────────────────────────────────────────────────────
            // Composite index for the duplicate-title-per-due_date business rule
            $table->unique(['title', 'due_date'], 'tasks_title_due_date_unique');

            // Individual indexes for common query patterns
            $table->index('status',   'tasks_status_index');
            $table->index('priority', 'tasks_priority_index');
            $table->index('due_date', 'tasks_due_date_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};