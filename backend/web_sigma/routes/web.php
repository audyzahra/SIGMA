<?php

use App\Http\Controllers\Web\AuthController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/access');

Route::get('/access', [AuthController::class, 'showSelection'])->name('login.selection');
Route::redirect('/login', '/access')->name('login');
Route::get('/login/{access}', [AuthController::class, 'showLogin'])->name('login.show');
Route::post('/login/{access}', [AuthController::class, 'login'])->name('login.store');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::get('/super-admin/dashboard', function () {
    return view('super_admin.dashboard');
})->middleware(['auth', 'role:super_admin'])->name('super-admin.dashboard');

Route::middleware(['auth', 'role:super_admin'])->prefix('super-admin')->name('super-admin.')->group(function () {
    Route::view('/users', 'super_admin.users')->name('users');
    Route::view('/roles', 'super_admin.roles')->name('roles');
    Route::view('/organizations', 'super_admin.organizations')->name('organizations');
    Route::view('/regions', 'super_admin.regions')->name('regions');
    Route::view('/data-source', 'super_admin.data_source')->name('data-source');
    Route::view('/ai-model', 'super_admin.ai_model')->name('ai-model');
    Route::view('/settings', 'super_admin.settings')->name('settings');
    Route::view('/audit', 'super_admin.audit')->name('audit');
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
