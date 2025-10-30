<?php

namespace App\Events;

use App\Models\Task;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TaskAssigned implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Task $task;
    public bool $isReassignment;

    /**
     * Create a new event instance.
     */
    public function __construct(Task $task, bool $isReassignment = false)
    {
        $this->task = $task->load(['user', 'status']);
        $this->isReassignment = $isReassignment;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.' . $this->task->user_id),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'task.assigned';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->task->id,
            'title' => $this->task->title,
            'description' => $this->task->description,
            'start_date' => $this->task->start_date->format('Y-m-d'),
            'end_date' => $this->task->end_date->format('Y-m-d'),
            'status' => $this->task->status->name,
            'reassigned' => $this->isReassignment,
            'assigned_at' => now()->toISOString(),
        ];
    }
}
