<?php

namespace App\Http\Controllers;

use App\Models\UserAvailability;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UserAvailabilityController extends Controller
{
    /**
     * Get user availability for a specific date range.
     */
    public function index(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $availabilities = UserAvailability::with(['user', 'task'])
            ->where('user_id', $validated['user_id'])
            ->whereBetween('date', [$validated['start_date'], $validated['end_date']])
            ->get();

        return Inertia::render('Availabilities/Index', [
            'availabilities' => $availabilities,
        ]);
    }
}
