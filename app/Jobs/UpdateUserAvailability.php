<?php

namespace App\Jobs;

use App\Models\Task;
use App\Models\UserAvailability;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;

class UpdateUserAvailability implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Task $task
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Delete existing availability records for this task
        UserAvailability::where('task_id', $this->task->id)->delete();

        // Generate availability records for each day in the task period
        $startDate = $this->task->start_date;
        $endDate = $this->task->end_date;
        $currentDate = $startDate->copy();

        $availabilityRecords = [];

        while ($currentDate->lte($endDate)) {
            // Check if this date already has an availability record for this user
            $existingAvailability = UserAvailability::where('user_id', $this->task->user_id)
                ->where('date', $currentDate->format('Y-m-d'))
                ->where('task_id', '!=', $this->task->id)
                ->exists();

            if ($existingAvailability) {
                // If there's a conflict, we might want to handle it
                // For now, we'll skip creating the record
                $currentDate->addDay();
                continue;
            }

            $availabilityRecords[] = [
                'user_id' => $this->task->user_id,
                'task_id' => $this->task->id,
                'date' => $currentDate->format('Y-m-d'),
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $currentDate->addDay();
        }

        // Bulk insert availability records
        if (!empty($availabilityRecords)) {
            UserAvailability::insert($availabilityRecords);
        }
    }
}
