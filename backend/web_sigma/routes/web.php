<?php

use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\Government\DashboardController;
use App\Http\Controllers\Web\Government\FireRiskController;
use App\Http\Controllers\Web\Government\ImpactController;
use App\Http\Controllers\Web\Government\PriorityController;
use App\Http\Controllers\Web\Government\RecommendationController;
use App\Http\Controllers\Web\public_sigma\AspirationController;
use App\Http\Controllers\Web\SuperAdmin\AIModelController;
use App\Http\Controllers\Web\SuperAdmin\AuditLogController;
use App\Http\Controllers\Web\SuperAdmin\DataSourceController;
use App\Http\Controllers\Web\SuperAdmin\OrganizationController;
use App\Http\Controllers\Web\SuperAdmin\RegionController;
use App\Http\Controllers\Web\SuperAdmin\RolePermissionController;
use App\Http\Controllers\Web\SuperAdmin\SystemConfigurationController;
use App\Http\Controllers\Web\SuperAdmin\UserManagementController;
use App\Http\Controllers\Web\SuperAdminDashboardController;
use App\Http\Controllers\Web\SuperAdmin\AspirationController as SuperAdminAspirationController;
use App\Models\SystemProfile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| AUTHENTICATION
|--------------------------------------------------------------------------
*/

Route::get('/access', [AuthController::class, 'showSelection'])
    ->name('login.selection');

Route::redirect('/login', '/access')
    ->name('login');

Route::get('/login/{access}', [AuthController::class, 'showLogin'])
    ->name('login.show');

Route::post('/login/{access}', [AuthController::class, 'login'])
    ->name('login.store');

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');


/*
|--------------------------------------------------------------------------
| SUPER ADMIN
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:super_admin'])
    ->prefix('super-admin')
    ->name('super-admin.')
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Dashboard
        |--------------------------------------------------------------------------
        */

        Route::get('/dashboard', [
            SuperAdminDashboardController::class,
            'index'
        ])->name('dashboard');


        /*
        |--------------------------------------------------------------------------
        | Export
        |--------------------------------------------------------------------------
        */

        Route::get('/export/audit', [
            SuperAdminDashboardController::class,
            'export'
        ])->name('export.audit');


        /*
        |--------------------------------------------------------------------------
        | Management
        |--------------------------------------------------------------------------
        */

        Route::resource('manage-users', UserManagementController::class)
            ->parameters([
                'manage-users' => 'user'
            ]);

        Route::resource('role-permissions', RolePermissionController::class)
            ->parameters([
                'role-permissions' => 'role'
            ]);

        Route::resource('organizations', OrganizationController::class);

        Route::resource('regions', RegionController::class);

        Route::resource('data-sources', DataSourceController::class);

        Route::resource('ai-models', AIModelController::class);

        Route::resource('configurations', SystemConfigurationController::class);

        Route::resource('audit-logs', AuditLogController::class)
            ->only([
                'index',
                'show'
            ]);

        Route::get('aspirations', 
            [SuperAdminAspirationController::class, 'index']
        )->name('aspirations.index');


        Route::get('aspirations/{hash}',
            [SuperAdminAspirationController::class, 'show']
        )->name('aspirations.show');

        Route::patch(
            'aspirations/{aspiration}/status',
            [SuperAdminAspirationController::class,'updateStatus']
        )->name('aspirations.status');
    });


/*
|--------------------------------------------------------------------------
| GOVERNMENT
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:government'])
    ->prefix('government')
    ->name('government.')
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Dashboard
        |--------------------------------------------------------------------------
        */

        Route::get('/dashboard', [
            DashboardController::class,
            'index'
        ])->name('dashboard');


        /*
        |--------------------------------------------------------------------------
        | Monitoring
        |--------------------------------------------------------------------------
        */

        Route::get('/fire-risk', [
            FireRiskController::class,
            'index'
        ])->name('fire-risk');

        Route::get('/impact', [
            ImpactController::class,
            'index'
        ])->name('impact');

        Route::get('/priority', [
            PriorityController::class,
            'index'
        ])->name('priority');

        Route::get('/recommendation', [
            RecommendationController::class,
            'index'
        ])->name('recommendation');
    });


/*
|--------------------------------------------------------------------------
| PUBLIC SIGMA
|--------------------------------------------------------------------------
*/

Route::name('public_sigma.')
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Homepage
        |--------------------------------------------------------------------------
        */

        Route::get('/', function () {

            $profile = SystemProfile::first();

            $totalHotspot = DB::table('hotspots')->count();

            $totalIncident = DB::table('incidents')->count();

            return view('public_sigma.index', compact(
                'profile',
                'totalHotspot',
                'totalIncident'
            ));

        })->name('index');


        /*
        |--------------------------------------------------------------------------
        | Aspirations
        |--------------------------------------------------------------------------
        */

        Route::post('/aspirations', [
            AspirationController::class,
            'store'
        ])->name('aspirations.store');
    });