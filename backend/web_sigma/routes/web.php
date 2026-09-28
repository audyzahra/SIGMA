<?php

use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\Government\DashboardController;
use App\Http\Controllers\Web\Government\FireRiskController;
use App\Http\Controllers\Web\Government\ImpactController;
use App\Http\Controllers\Web\Government\PriorityController;
use App\Http\Controllers\Web\Government\RecommendationController;
use App\Http\Controllers\Web\Government\CitizenReportController;
use App\Http\Controllers\Web\Government\ResponseAssignmentController;
use App\Http\Controllers\Web\Government\FieldTeamController;

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

use App\Http\Controllers\Api\RegionController as ApiRegionController;
use App\Http\Controllers\Api\HotspotController;

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
            ->parameters([
                'audit-logs' => 'auditLog'
            ])
            ->only([
                'index',
                'show'
            ]);

        Route::get(
            'aspirations',
            [SuperAdminAspirationController::class, 'index']
        )->name('aspirations.index');

        Route::get(
            'aspirations/{hash}',
            [SuperAdminAspirationController::class, 'show']
        )->name('aspirations.show');

        Route::patch(
            'aspirations/{aspiration}/status',
            [SuperAdminAspirationController::class, 'updateStatus']
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

        /*
        |--------------------------------------------------------------------------
        | GIS Region Children
        |--------------------------------------------------------------------------
        */

        Route::get('/fire-risk/{id}/children', [
            FireRiskController::class,
            'children'
        ])->name('fire-risk.children');


        /*
        |--------------------------------------------------------------------------
        | Perbarui Analisis Risiko (AI Service)
        |--------------------------------------------------------------------------
        */

        Route::post('/fire-risk/ai-refresh', [
            FireRiskController::class,
            'aiRefresh'
        ])->name('fire-risk.ai-refresh');

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

        /*
        |--------------------------------------------------------------------------
        | Response Assignment
        |--------------------------------------------------------------------------
        */

        Route::post('/recommendation/assign', [
            ResponseAssignmentController::class,
            'store'
        ])->name('recommendation.assign');

        /*
        |--------------------------------------------------------------------------
        | Laporan Masyarakat
        |--------------------------------------------------------------------------
        */

        Route::get(
            'reports',
            [CitizenReportController::class, 'index']
        )->name('reports.index');

        Route::get(
            'reports/{hash}/history',
            [CitizenReportController::class, 'history']
        )->name('reports.history');

        Route::get(
            'reports/{hash}',
            [CitizenReportController::class, 'show']
        )->name('reports.show');

        Route::patch(
            'reports/{report}/verify',
            [CitizenReportController::class, 'verify']
        )->name('reports.verify');

        Route::patch(
            'reports/{report}/reject',
            [CitizenReportController::class, 'reject']
        )->name('reports.reject');

        Route::post(
            'reports/assign',
            [CitizenReportController::class, 'assign']
        )->name('reports.assign');


        /*
        |--------------------------------------------------------------------------
        | Field Team Management
        |--------------------------------------------------------------------------
        */

        Route::get('/field-teams', [
            FieldTeamController::class,
            'index'
        ])->name('field-teams.index');

        Route::get('/field-teams/{team}', [
            FieldTeamController::class,
            'show'
        ])->name('field-teams.show');

        Route::get('/field-teams/create', [
            FieldTeamController::class,
            'create'
        ])->name('field-teams.create');


        Route::post('/field-teams', [
            FieldTeamController::class,
            'store'
        ])->name('field-teams.store');


        Route::get('/field-teams/{team}/edit', [
            FieldTeamController::class,
            'edit'
        ])->name('field-teams.edit');


        Route::put('/field-teams/{team}', [
            FieldTeamController::class,
            'update'
        ])->name('field-teams.update');


        Route::delete('/field-teams/{team}', [
            FieldTeamController::class,
            'destroy'
        ])->name('field-teams.destroy');
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

    use App\Http\Controllers\AITestController;


    Route::get(
        '/ai-test',
        [AITestController::class,'test']
    );

    Route::prefix('regions')->group(function(){


        /*
        |--------------------------------------------------------------------------
        | Semua Provinsi
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/provinces',
            [
                ApiRegionController::class,
                'provinces'
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Child Wilayah
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/{id}/children',
            [
                ApiRegionController::class,
                'children'
            ]
        );

        Route::get(
            '/hotspots',
            [
                HotspotController::class,
                'index'
            ]
        );


    });
