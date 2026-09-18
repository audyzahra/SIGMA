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
})->middleware(['auth', 'role:super_admin']);

Route::get('/government/dashboard', function () {
    return view('government.dashboard');
})->middleware(['auth', 'role:government']);
