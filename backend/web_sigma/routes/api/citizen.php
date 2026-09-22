<?php

use App\Http\Controllers\Api\Citizen\DashboardController;
use App\Http\Controllers\Api\Citizen\MapController;
use App\Http\Controllers\Api\Citizen\NotificationController;
use App\Http\Controllers\Api\Citizen\NotificationStreamController;
use App\Http\Controllers\Api\Citizen\ProfileController;
use App\Http\Controllers\Api\Citizen\ReportController;
use Illuminate\Support\Facades\Route;

Route::middleware('role:citizen')->prefix('citizen')->group(function (): void {
    Route::get('/dashboard', DashboardController::class);
    Route::get('/reports', [ReportController::class, 'index']);
    Route::post('/reports', [ReportController::class, 'store']);
    Route::get('/reports/{report}', [ReportController::class, 'show']);
    Route::get('/map', MapController::class);
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/stream', NotificationStreamController::class);
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
    Route::patch('/notifications/read-all', [NotificationController::class, 'markAllRead']);
    Route::patch('/notifications/{notification}/read', [NotificationController::class, 'markRead']);
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::patch('/profile', [ProfileController::class, 'update']);
});
