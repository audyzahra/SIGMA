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
use App\Http\Controllers\Web\Government\AccountController;
use App\Http\Controllers\Web\Government\NotificationController;

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

        /*
        |--------------------------------------------------------------------------
        | Analisis Dampak (Spatial)
        |--------------------------------------------------------------------------
        */

        Route::get('/impact/analysis', [
            ImpactController::class,
            'analysis'
        ])->name('impact.analysis');

        Route::post('/impact/analysis', [
            ImpactController::class,
            'analyze'
        ])->name('impact.analyze');

        /*
        |--------------------------------------------------------------------------
        | Prioritas Penanganan
        |--------------------------------------------------------------------------
        |
        | Ranking wilayah berdasarkan risiko karhutla dan dampak yang
        | ditimbulkan. Semua angka berasal dari PriorityCalculationService.
        |
        */

        Route::get('/priority', [
            PriorityController::class,
            'index'
        ])->name('priority');

        /* Hitung ulang prioritas seluruh wilayah */
        Route::post('/priority/recalculate', [
            PriorityController::class,
            'recalculate'
        ])->name('priority.recalculate');

        /* Data JSON satu wilayah (konsumsi AI Service / GIS) */
        Route::get('/priority/{priority}/data', [
            PriorityController::class,
            'payload'
        ])->name('priority.payload');

        /* Detail prioritas satu wilayah */
        Route::get('/priority/{priority}', [
            PriorityController::class,
            'show'
        ])->name('priority.show');

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


        Route::get('/field-teams/create', [
            FieldTeamController::class,
            'create'
        ])->name('field-teams.create');


        Route::post('/field-teams', [
            FieldTeamController::class,
            'store'
        ])->name('field-teams.store');


        Route::get('/field-teams/{team}', [
            FieldTeamController::class,
            'show'
        ])->name('field-teams.show');


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

        /*
        |--------------------------------------------------------------------------
        | Account
        |--------------------------------------------------------------------------
        */

        Route::get('/account', [
            AccountController::class,
            'index'
        ])->name('account.index');

        Route::get('/account/profile', [
            AccountController::class,
            'profile'
        ])->name('account.profile');

        Route::put('/account/profile', [
            AccountController::class,
            'updateProfile'
        ])->name('account.profile.update');

        Route::get('/account/password', [
            AccountController::class,
            'password'
        ])->name('account.password');

        Route::put('/account/password', [
            AccountController::class,
            'updatePassword'
        ])->name('account.password.update');


        Route::get('/account/appearance', [
            AccountController::class,
            'appearance'
        ])->name('account.appearance');

        Route::put('/account/appearance', [
            AccountController::class,
            'updateAppearance'
        ])->name('account.appearance.update');

        Route::get('/account/notifications', [
            AccountController::class,
            'notifications'
        ])->name('account.notifications');


        Route::put('/account/notifications', [
            AccountController::class,
            'updateNotifications'
        ])->name('account.notifications.update');

        Route::get('/notifications', [
            NotificationController::class,
            'index'
        ])->name('notifications.index');


        Route::post('/notifications/{id}/read', [
            NotificationController::class,
            'read'
        ])->name('notifications.read');


        Route::post('/notifications/read-all', [
            NotificationController::class,
            'readAll'
        ])->name('notifications.readAll');

        Route::get('/account/activity', [
            AccountController::class,
            'activity'
        ])->name('account.activity');
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

            /*
            |--------------------------------------------------------------------------
            | PROFILE
            |--------------------------------------------------------------------------
            */

            $profile = SystemProfile::first();


            /*
            |--------------------------------------------------------------------------
            | STATISTIK
            |--------------------------------------------------------------------------
            */

            $totalHotspot = DB::table('hotspots')->count();

            $totalIncident = DB::table('incidents')->count();


            /*
            |--------------------------------------------------------------------------
            | REGION
            |--------------------------------------------------------------------------
            */

            $regions = \App\Models\Region::query()
                ->whereIn('level', [
                    'province',
                    'regency',
                    'district'
                ])
                ->select([
                    'id',
                    'parent_id',
                    'name',
                    'code',
                    'level'
                ])
                ->orderBy('level')
                ->orderBy('name')
                ->get();


            /*
            |--------------------------------------------------------------------------
            | GEOMETRY
            |--------------------------------------------------------------------------
            */

            $geometryData = DB::table('regions')
                ->whereIn('level', [
                    'province',
                    'regency',
                    'district'
                ])
                ->select([
                    'id'
                ])
                ->selectRaw(
                    'ST_AsGeoJSON(geometry) AS geometry_json'
                )
                ->get()
                ->keyBy('id');


            $regions->each(function ($region) use ($geometryData) {

                $geometry = $geometryData->get($region->id);

                $region->setAttribute(
                    'gis_geometry',
                    $geometry?->geometry_json
                );

            });


            /*
            |--------------------------------------------------------------------------
            | FIRE RISK
            |--------------------------------------------------------------------------
            */

            $fireRisks = \App\Models\FireRisk::latest(
                'calculated_at'
            )->get();

            $riskByRegion = $fireRisks->keyBy(
                'region_id'
            );


            /*
            |--------------------------------------------------------------------------
            | DATA PETA
            |--------------------------------------------------------------------------
            */

            $sigmaRegions = $regions->map(
                function ($region) use ($riskByRegion) {

                    $risk = $riskByRegion->get(
                        $region->id
                    );

                    return [
                        'id' => $region->id,

                        'name' => $region->name,

                        'code' => $region->code,

                        'level' => $region->level,

                        'parent_id' => $region->parent_id,

                        'geometry' => $region->gis_geometry,

                        'risk_score' =>
                            $risk?->risk_score ?? 0,

                        'risk_level' =>
                            $risk?->risk_level ?? 'LOW',

                        'temperature' =>
                            $risk?->temperature,

                        'humidity' =>
                            $risk?->humidity,

                        'wind_speed' =>
                            $risk?->wind_speed,

                        'rainfall' =>
                            $risk?->rainfall,
                    ];

                }
            )->values();


            /*
            |--------------------------------------------------------------------------
            | RETURN VIEW
            |--------------------------------------------------------------------------
            */

            return view(
                'public_sigma.index',
                compact(
                    'profile',
                    'totalHotspot',
                    'totalIncident',
                    'sigmaRegions'
                )
            );

        })->name('index');


        /*
        |--------------------------------------------------------------------------
        | ASPIRATIONS
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
    [AITestController::class, 'test']
);

Route::prefix('regions')->group(function () {


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
