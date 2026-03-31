<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    // ─── Table ───────────────────────────────────────────────────────────────
    protected $table = 'tasks';

    // ─── Mass Assignable Fields ───────────────────────────────────────────────
    protected $fillable = [
        'title',
        'due_date',
        'priority',
        'status',
    ];

    // ─── Casts ────────────────────────────────────────────────────────────────
    protected $casts = [
        'due_date'   => 'date:Y-m-d',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ─── Enums ───────────────────────────────────────────────────────────────
    // Priority order used for sorting: higher number = higher sort priority
    const PRIORITY_ORDER = [
        'high'   => 3,
        'medium' => 2,
        'low'    => 1,
    ];

    // Status progression — each status can only move to the next one
    const STATUS_TRANSITIONS = [
        'pending'     => 'in_progress',
        'in_progress' => 'done',
    ];

    // ─── Helpers ─────────────────────────────────────────────────────────────

    /**
     * Returns the next valid status for this task,
     * or null if the task is already at the final status.
     */
    public function nextStatus(): ?string
    {
        return self::STATUS_TRANSITIONS[$this->status] ?? null;
    }

    /**
     * Check whether a given status transition is valid.
     */
    public function canTransitionTo(string $newStatus): bool
    {
        return $this->nextStatus() === $newStatus;
    }
}