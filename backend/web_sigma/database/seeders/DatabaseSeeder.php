<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            RegionSeeder::class,
            OrganizationSeeder::class,
            UserSeeder::class,
            DataSourceSeeder::class,
            SystemProfileSeeder::class,
            SystemConfigurationSeeder::class,
            AiModelSeeder::class,
            FireRiskSeeder::class,
            FireRiskHistorySeeder::class,
            FireStatisticSeeder::class,
            HotspotSeeder::class,
            IncidentSeeder::class,
            CitizenReportSeeder::class,
            ImpactAssessmentSeeder::class,
            IncidentPrioritySeeder::class,
            AiResponseRecommendationSeeder::class,
            FieldTeamSeeder::class,
            ResponseAssignmentSeeder::class,
            FieldAssignmentSeeder::class,
            TeamNavigationLogSeeder::class,
            IncidentStatusHistorySeeder::class,
            FieldVerificationSeeder::class,
            OfflineSyncQueueSeeder::class,
            AiReportVerificationSeeder::class,
            VoiceReportSeeder::class,
            ReportStatusHistorySeeder::class,
            ReportRewardSeeder::class,
            MediaFileSeeder::class,
            AuditLogSeeder::class,
        ]);
    }
}
