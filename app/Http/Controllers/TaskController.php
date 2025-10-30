<?php

namespace App\Http\Controllers;

use App\Events\TaskAssigned;
use App\Jobs\UpdateUserAvailability;
use App\Models\Task;
use App\Models\TaskStatus;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $query = Task::with(['user', 'status']);

        if ($request->user()->role !== 'admin') {
            $query->where('user_id', $request->user()->id);
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status_id', $request->input('status'));
        }

        if ($request->user()->role === 'admin' && $request->filled('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        $tasks = $query->latest()->paginate(20);

        return Inertia::render('Dashboard', [
            'tasks' => $tasks,
            'users' => User::all(['id', 'name', 'email']),
            'statuses' => TaskStatus::all(['id', 'name', 'color']),
            'filters' => $request->only(['search', 'status', 'user_id']),
        ]);
    }

    public function store(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            abort(403, 'Only administrators can create tasks.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'user_id' => 'required|exists:users,id',
            'status_id' => 'required|exists:task_statuses,id',
        ]);

        $hasOverlap = Task::where('user_id', $validated['user_id'])
            ->where(function ($query) use ($validated) {
                $query->whereBetween('start_date', [$validated['start_date'], $validated['end_date']])
                    ->orWhereBetween('end_date', [$validated['start_date'], $validated['end_date']])
                    ->orWhere(function ($q) use ($validated) {
                        $q->where('start_date', '<=', $validated['start_date'])
                            ->where('end_date', '>=', $validated['end_date']);
                    });
            })
            ->exists();

        if ($hasOverlap) {
            throw ValidationException::withMessages([
                'user_id' => 'This user already has an overlapping task during the selected period.',
            ]);
        }

        $task = Task::create($validated);

        UpdateUserAvailability::dispatch($task);

        event(new TaskAssigned($task, false));

        return redirect()->route('tasks.index')->with('success', 'Task created successfully.');
    }

    public function update(Request $request, Task $task)
    {
        if ($request->user()->role !== 'admin') {
            abort(403, 'Only administrators can update tasks.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'user_id' => 'required|exists:users,id',
            'status_id' => 'required|exists:task_statuses,id',
        ]);

        $oldUserId = $task->user_id;
        $userIdChanged = $task->user_id !== $validated['user_id'];
        $datesChanged = $task->start_date !== $validated['start_date'] || $task->end_date !== $validated['end_date'];

        if ($userIdChanged || $datesChanged) {
            $hasOverlap = Task::where('user_id', $validated['user_id'])
                ->where('id', '!=', $task->id)
                ->where(function ($query) use ($validated) {
                    $query->whereBetween('start_date', [$validated['start_date'], $validated['end_date']])
                        ->orWhereBetween('end_date', [$validated['start_date'], $validated['end_date']])
                        ->orWhere(function ($q) use ($validated) {
                            $q->where('start_date', '<=', $validated['start_date'])
                                ->where('end_date', '>=', $validated['end_date']);
                        });
                })
                ->exists();

            if ($hasOverlap) {
                throw ValidationException::withMessages([
                    'user_id' => 'This user already has an overlapping task during the selected period.',
                ]);
            }
        }

        $task->update($validated);

        UpdateUserAvailability::dispatch($task);

        if ($userIdChanged) {
            $task->refresh();
            event(new TaskAssigned($task, true));
        }

        return redirect()->route('tasks.index')->with('success', 'Task updated successfully.');
    }

    public function destroy(Task $task)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Only administrators can delete tasks.');
        }

        $task->availabilities()->delete();

        $task->delete();

        return redirect()->route('tasks.index')->with('success', 'Task deleted successfully.');
    }
}
