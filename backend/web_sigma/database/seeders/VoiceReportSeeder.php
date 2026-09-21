<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\VoiceReport;
use App\Models\User;
use App\Models\CitizenReport;

class VoiceReportSeeder extends Seeder
{
    public function run(): void
    {
        $citizen = User::where('email', 'masyarakat@sigma.id')->first();

        $reports = CitizenReport::orderBy('id')->get();

        foreach ($reports as $index => $report) {
            VoiceReport::firstOrCreate(
                [
                    'citizen_report_id' => $report->id,
                ],
                [
                    'user_id' => $citizen?->id,
                    'audio_file' => 'voice_reports/report_' . $report->id . '.mp3',
                    'transcript' => 'Terdeteksi adanya indikasi kebakaran di sekitar lokasi laporan.',
                    'detected_location' => null,
                    'processing_status' => 'processed',
                ]
            );
        }
    }
}
