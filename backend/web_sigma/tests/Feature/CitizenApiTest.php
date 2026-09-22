<?php

namespace Tests\Feature;

use App\Models\CitizenReport;
use App\Models\ReportStatusHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CitizenApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_citizen_can_create_and_only_read_own_reports(): void
    {
        Role::create(['name' => 'citizen', 'guard_name' => 'web']);
        $citizen = User::factory()->create();
        $citizen->assignRole('citizen');
        $otherCitizen = User::factory()->create();
        $otherCitizen->assignRole('citizen');

        $created = $this->actingAs($citizen, 'sanctum')->postJson('/api/citizen/reports', [
            'report_type' => 'fire',
            'latitude' => -6.2,
            'longitude' => 108.3,
            'description' => 'Asap terlihat dari lahan terbuka.',
        ]);

        $created->assertCreated()->assertJsonPath('report.status', 'submitted');
        $reportId = $created->json('report.id');

        $this->actingAs($citizen, 'sanctum')->getJson("/api/citizen/reports/{$reportId}")->assertOk();
        $this->actingAs($otherCitizen, 'sanctum')->getJson("/api/citizen/reports/{$reportId}")->assertNotFound();
    }

    public function test_citizen_dashboard_requires_an_authenticated_citizen(): void
    {
        $this->getJson('/api/citizen/dashboard')->assertUnauthorized();
    }

    public function test_status_updates_create_a_database_notification_that_can_be_read(): void
    {
        Role::create(['name' => 'citizen', 'guard_name' => 'web']);
        $citizen = User::factory()->create();
        $citizen->assignRole('citizen');
        $report = CitizenReport::create([
            'user_id' => $citizen->id,
            'report_type' => 'fire',
            'latitude' => -6.2,
            'longitude' => 108.3,
            'verification_status' => 'pending',
        ]);

        ReportStatusHistory::create([
            'citizen_report_id' => $report->id,
            'status' => 'process',
            'description' => 'Petugas mulai menangani laporan.',
        ]);

        $this->actingAs($citizen, 'sanctum')
            ->getJson('/api/citizen/notifications')
            ->assertOk()
            ->assertJsonPath('unread_count', 1)
            ->assertJsonPath('notifications.0.report_id', (string) $report->id)
            ->assertJsonPath('notifications.0.status', 'process');

        $id = $citizen->unreadNotifications()->value('id');
        $this->actingAs($citizen, 'sanctum')
            ->patchJson("/api/citizen/notifications/{$id}/read")
            ->assertOk()
            ->assertJsonPath('unread_count', 0);
    }
}
