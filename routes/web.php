<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AgentController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

// route auth dari Breeze
require __DIR__.'/auth.php';

// Dashboard wajib login
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    // Full chat page per agent
    Route::get('/agents/{agent}/chat', [AgentController::class, 'chat'])->name('agents.chat');
    Route::get('/agents/{agent}/edit', [AgentController::class, 'edit'])->name('agents.edit');
    Route::put('/agents/{agent}', [AgentController::class, 'update'])->name('agents.update');
    Route::delete('/agents/{agent}', [AgentController::class, 'destroy'])->name('agents.destroy');
    Route::post('/agents/{agent}/warm', [AgentController::class, 'warm'])->name('agents.warm');
    Route::post('/agents/{agent}/run', [AgentController::class, 'run'])->name('agents.run');
});
