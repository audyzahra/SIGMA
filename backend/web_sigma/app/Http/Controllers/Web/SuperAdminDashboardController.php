<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\FireRisk;
use App\Models\Organization;
use App\Models\Region;
use App\Models\SystemConfiguration;
use App\Models\User;
use Illuminate\Http\Response;

class SuperAdminDashboardController extends Controller
{
    public function index()
    {
        $risks = FireRisk::query()
            ->selectRaw('risk_level, count(*) as total')
            ->groupBy('risk_level')
            ->pluck('total', 'risk_level');

        $systemStatus = SystemConfiguration::query()
            ->where('key', 'system_status')
            ->value('value') ?? 'Belum dikonfigurasi';

        return view('super_admin.dashboard', [
            'userCount' => User::count(),
            'activeUserCount' => User::whereNotNull('email_verified_at')->count(),
            'organizationCount' => Organization::count(),
            'activeOrganizationCount' => Organization::where('status', 'active')->count(),
            'regionCount' => Region::count(),
            'regionLevels' => Region::query()->selectRaw('level, count(*) as total')->groupBy('level')->pluck('total', 'level'),
            'systemStatus' => $systemStatus,
            'recentActivities' => AuditLog::with('user')->latest()->take(5)->get(),
            'auditActivityByDay' => AuditLog::query()
                ->where('created_at', '>=', now()->subDays(6)->startOfDay())
                ->selectRaw('DATE(created_at) as date, COUNT(*) as total')
                ->groupBy('date')
                ->pluck('total', 'date'),
            'riskCounts' => $risks,
            'extremeRisks' => FireRisk::with('region')->where('risk_level', 'extreme')->latest('calculated_at')->take(3)->get(),
        ]);
    }

    public function export(): Response
    {
        $rows = AuditLog::with('user')->latest()->get()->map(fn (AuditLog $log) => [
            $log->created_at?->format('Y-m-d H:i:s'),
            $log->user?->name ?? 'Sistem', $log->action, $log->module, $log->description, $log->ip_address,
        ]);

        $csv = "Waktu,User,Aksi,Modul,Deskripsi,IP\n";
        foreach ($rows as $row) {
            $csv .= implode(',', array_map(fn ($value) => '"'.str_replace('"', '""', (string) $value).'"', $row))."\n";
        }

        return response($csv, 200, ['Content-Type' => 'text/csv', 'Content-Disposition' => 'attachment; filename="sigma-audit-report.csv"']);
    }
}
