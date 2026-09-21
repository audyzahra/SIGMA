<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ReportReward;
use App\Models\CitizenReport;
use App\Models\User;

class ReportRewardSeeder extends Seeder
{
    public function run(): void
    {
        $reports = CitizenReport::orderBy('id')->get();

        $citizen = User::where('email', 'masyarakat@sigma.id')->first();

        $admin = User::where('email', 'superadmin@sigma.id')->first();

        if (!$citizen || !$admin) {
            return;
        }

        foreach ($reports as $report) {
            ReportReward::firstOrCreate(
                [
                    'citizen_report_id' => $report->id,
                    'user_id' => $citizen->id,
                ],
                [
                    'reward_type' => 'point',
                    'amount' => 100,
                    'status' => 'approved',
                    'approved_by' => $admin->id,
                    'approved_at' => now(),
                ]
            );
        }
    }
}
