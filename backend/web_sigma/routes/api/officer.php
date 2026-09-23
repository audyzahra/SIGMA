<?php

use App\Http\Controllers\Api\Officer\DashboardController;
use App\Http\Controllers\Api\Officer\MapController;
use App\Http\Controllers\Api\Officer\NotificationController;
use App\Http\Controllers\Api\Officer\ProfileController;
use App\Http\Controllers\Api\Officer\ReportController;
use App\Http\Controllers\Api\Officer\TaskController;
use Illuminate\Support\Facades\Route;

Route::middleware('role:officer')->prefix('officer')->group(function (): void {
    Route::get('/dashboard', DashboardController::class);
    Route::get('/tasks', [TaskController::class, 'index']);
    Route::get('/tasks/{task}', [TaskController::class, 'show']);
    Route::patch('/tasks/{task}/accept', [TaskController::class, 'accept']);
    Route::patch('/tasks/{task}/status', [TaskController::class, 'updateStatus']);
    Route::get('/map', MapController::class);
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::patch('/notifications/{notification}/read', [NotificationController::class, 'markRead']);
    Route::get('/reports', [ReportController::class, 'index']);
    Route::post('/tasks/{task}/reports', [ReportController::class, 'store']);
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::post('/profile/heartbeat', [ProfileController::class, 'heartbeat']);
});
