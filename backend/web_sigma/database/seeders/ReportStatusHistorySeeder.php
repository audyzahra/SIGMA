<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ReportStatusHistory;
use App\Models\CitizenReport;
use App\Models\User;

class ReportStatusHistorySeeder extends Seeder
{
    public function run(): void
    {
        $reports = CitizenReport::orderBy('id')->get();

        $officer = User::where('email', 'petugas@sigma.id')->first();

        foreach ($reports as $report) {
            ReportStatusHistory::firstOrCreate(
                [
                    'citizen_report_id' => $report->id,
                    'status' => 'submitted',
                ],
                [
                    'description' => 'Laporan masyarakat berhasil diterima oleh sistem SIGMA.',
                    'updated_by' => $officer?->id,
                ]
            );
        }
    }
}
