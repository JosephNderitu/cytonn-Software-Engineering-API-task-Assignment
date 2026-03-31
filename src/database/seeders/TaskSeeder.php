<?php

namespace Database\Seeders;

use App\Models\Task;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class TaskSeeder extends Seeder
{
    /**
     * Seed the tasks table with realistic demo data
     * covering all priorities and statuses.
     */
    public function run(): void
    {
        $today = Carbon::today()->format('Y-m-d');
        $tomorrow = Carbon::tomorrow()->format('Y-m-d');
        $nextWeek = Carbon::today()->addDays(7)->format('Y-m-d');
        $nextMonth = Carbon::today()->addDays(30)->format('Y-m-d');

        $tasks = [
            // ─── High priority ────────────────────────────────────────────
            [
                'title'    => 'Fix critical production bug',
                'due_date' => $today,
                'priority' => 'high',
                'status'   => 'in_progress',
            ],
            [
                'title'    => 'Deploy hotfix to staging',
                'due_date' => $tomorrow,
                'priority' => 'high',
                'status'   => 'pending',
            ],
            [
                'title'    => 'Security audit review',
                'due_date' => $nextWeek,
                'priority' => 'high',
                'status'   => 'done',
            ],

            // ─── Medium priority ──────────────────────────────────────────
            [
                'title'    => 'Write unit tests for auth module',
                'due_date' => $tomorrow,
                'priority' => 'medium',
                'status'   => 'pending',
            ],
            [
                'title'    => 'Code review for PR #42',
                'due_date' => $nextWeek,
                'priority' => 'medium',
                'status'   => 'in_progress',
            ],
            [
                'title'    => 'Update API documentation',
                'due_date' => $nextWeek,
                'priority' => 'medium',
                'status'   => 'done',
            ],

            // ─── Low priority ─────────────────────────────────────────────
            [
                'title'    => 'Refactor legacy helper functions',
                'due_date' => $nextMonth,
                'priority' => 'low',
                'status'   => 'pending',
            ],
            [
                'title'    => 'Update README with new setup steps',
                'due_date' => $nextMonth,
                'priority' => 'low',
                'status'   => 'done',
            ],
        ];

        foreach ($tasks as $task) {
            Task::create($task);
        }

        $this->command->info('✅  TaskSeeder: ' . count($tasks) . ' tasks seeded.');
    }
}