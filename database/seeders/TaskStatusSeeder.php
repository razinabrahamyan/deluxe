<?php

namespace Database\Seeders;

use App\Models\TaskStatus;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TaskStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            ['name' => 'To Do', 'color' => '#6B7280'],
            ['name' => 'In Progress', 'color' => '#3B82F6'],
            ['name' => 'Completed', 'color' => '#10B981'],
            ['name' => 'Cancelled', 'color' => '#EF4444'],
        ];

        foreach ($statuses as $status) {
            TaskStatus::create($status);
        }
    }
}
