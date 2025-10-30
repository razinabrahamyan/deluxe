<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\UserAvailabilityController;
use Illuminate\Support\Facades\Route;

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Dashboard
    Route::get('/dashboard', [TaskController::class, 'index'])->name('tasks.index');

    // Tasks (no separate create page - using modal)
    Route::post('/tasks', [TaskController::class, 'store'])->name('tasks.store');
    Route::put('/tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');
    Route::delete('/tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');

    // User Availability
    Route::get('/availabilities', [UserAvailabilityController::class, 'index'])->name('availabilities.index');
});

// Default redirect
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('tasks.index');
    }
    return redirect()->route('login');
});
