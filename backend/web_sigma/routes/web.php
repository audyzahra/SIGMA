<?php

use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\SuperAdmin\AIModelController;
use App\Http\Controllers\Web\SuperAdmin\AuditLogController;
use App\Http\Controllers\Web\SuperAdmin\DataSourceController;
use App\Http\Controllers\Web\SuperAdmin\OrganizationController;
use App\Http\Controllers\Web\SuperAdmin\RegionController;
use App\Http\Controllers\Web\SuperAdmin\RolePermissionController;
use App\Http\Controllers\Web\SuperAdmin\SystemConfigurationController;
use App\Http\Controllers\Web\SuperAdmin\UserManagementController;
use App\Http\Controllers\Web\SuperAdminDashboardController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/access');

Route::get('/access', [AuthController::class, 'showSelection'])->name('login.selection');
Route::redirect('/login', '/access')->name('login');
Route::get('/login/{access}', [AuthController::class, 'showLogin'])->name('login.show');
Route::post('/login/{access}', [AuthController::class, 'login'])->name('login.store');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::get('/super-admin/dashboard', [SuperAdminDashboardController::class, 'index'])
    ->middleware(['auth', 'role:super_admin'])->name('super-admin.dashboard');
Route::get('/super-admin/export/audit', [SuperAdminDashboardController::class, 'export'])
    ->middleware(['auth', 'role:super_admin'])->name('super-admin.export.audit');

Route::middleware(['auth', 'role:super_admin'])->prefix('super-admin')->name('super-admin.')->group(function () {
    Route::resource('manage-users', UserManagementController::class)->parameters(['manage-users' => 'user']);
    Route::resource('role-permissions', RolePermissionController::class)->parameters(['role-permissions' => 'role']);
    Route::resource('organizations', OrganizationController::class);
    Route::resource('regions', RegionController::class);
    Route::resource('data-sources', DataSourceController::class);
    Route::resource('ai-models', AIModelController::class);
    Route::resource('configurations', SystemConfigurationController::class);
    Route::resource('audit-logs', AuditLogController::class)->only(['index', 'show']);
});

Route::get('/government/dashboard', function () {
    return view('government.dashboard');
})->middleware(['auth', 'role:government'])->name('government.dashboard');

Route::middleware(['auth', 'role:government'])->prefix('government')->name('government.')->group(function () {
    Route::view('/fire-risk', 'government.fire-risk')->name('fire-risk');
    Route::view('/impact', 'government.impact')->name('impact');
    Route::view('/priority', 'government.priority')->name('priority');
    Route::view('/recommendation', 'government.recommendation')->name('recommendation');
});
