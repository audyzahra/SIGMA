<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CitizenReport;
use App\Models\User;
use App\Models\Incident;

class CitizenReportSeeder extends Seeder
{
    public function run(): void
    {
        $citizen = User::where(
            'email',
            'masyarakat@sigma.id'
        )->first();

        $incident = Incident::where(
            'source_type',
            'report'
        )->first();

        if (!$citizen) {
            return;
        }

        $reports = [
            [
                'user_id' => $citizen->id,
                'incident_id' => $incident?->id,
                'report_type' => 'fire',
                'latitude' => -6.41235000,
                'longitude' => 108.29587000,
                'photo' => null,
                'video' => null,
                'description' => 'Terlihat adanya kebakaran di area pertanian.',
                'verification_status' => 'pending',
            ],
            [
                'user_id' => $citizen->id,
                'incident_id' => null,
                'report_type' => 'smoke',
                'latitude' => -6.39012000,
                'longitude' => 108.34025000,
                'photo' => null,
                'video' => null,
                'description' => 'Masyarakat melihat kepulan asap dari area lahan terbuka.',
                'verification_status' => 'pending',
            ],
            [
                'user_id' => $citizen->id,
                'incident_id' => null,
                'report_type' => 'burning_activity',
                'latitude' => -6.35045000,
                'longitude' => 108.37012000,
                'photo' => null,
                'video' => null,
                'description' => 'Terlihat aktivitas pembakaran di sekitar area lahan.',
                'verification_status' => 'verified',
            ],
        ];

        foreach ($reports as $report) {
            CitizenReport::create($report);
        }
    }
}
