<?php

namespace Database\Seeders;

use App\Models\Task;
use App\Models\TaskStatus;
use App\Models\User;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = TaskStatus::pluck('id')->all();

        // Iterate through all non-admin users
        $users = User::where('email', '!=', 'admin@example.com')->get();

        foreach ($users as $user) {
            // Create 0-3 tasks per user
            $numTasks = random_int(0, 3);

            $currentStart = now()->startOfDay()->addDays(random_int(0, 10));

            for ($i = 1; $i <= $numTasks; $i++) {
                $duration = random_int(1, 5); // 1-5 days
                $start = (clone $currentStart);
                $end = (clone $start)->addDays($duration - 1);

                Task::create([
                    'title' => "Task {$i} for {$user->name}",
                    'description' => random_int(0, 1) ? 'Seeded demo task description.' : null,
                    'start_date' => $start->toDateString(),
                    'end_date' => $end->toDateString(),
                    'user_id' => $user->id,
                    'status_id' => $statuses[array_rand($statuses)],
                ]);

                // Move next start after this task to avoid overlaps
                $currentStart = (clone $end)->addDays(random_int(1, 3));
            }
        }
    }
}


