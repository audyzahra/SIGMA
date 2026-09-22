<?php

namespace App\Services\Citizen;

use App\Models\ReportStatusHistory;
use App\Notifications\CitizenReportStatusChanged;

class CitizenNotificationService
{
    public function reportStatusChanged(ReportStatusHistory $history): void
    {
        $report = $history->citizenReport()->with('user')->first();
        if (! $report?->user) {
            return;
        }

        $report->user->notify(new CitizenReportStatusChanged(
            $report,
            $history->status,
            $history->description,
        ));
    }
}
