<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FieldTeam;
use App\Models\Organization;

class FieldTeamSeeder extends Seeder
{
    public function run(): void
    {
        $teamOrganization = Organization::where(
            'type',
            'team'
        )->first();

        if (!$teamOrganization) {
            return;
        }

        $teams = [
            [
                'team_name' => 'Tim Rescue SIGMA',
                'leader_name' => 'Koordinator Tim SIGMA',
                'phone' => '081234567894',
                'status' => 'active',
            ],
            [
                'team_name' => 'Tim Pemadam Karhutla',
                'leader_name' => 'Koordinator Pemadam',
                'phone' => '081234567895',
                'status' => 'active',
            ],
            [
                'team_name' => 'Tim Evakuasi SIGMA',
                'leader_name' => 'Koordinator Evakuasi',
                'phone' => '081234567896',
                'status' => 'active',
            ],
        ];

        foreach ($teams as $team) {
            FieldTeam::firstOrCreate(
                [
                    'organization_id' => $teamOrganization->id,
                    'team_name' => $team['team_name'],
                ],
                [
                    'leader_name' => $team['leader_name'],
                    'phone' => $team['phone'],
                    'status' => $team['status'],
                ]
            );
        }
    }
}
