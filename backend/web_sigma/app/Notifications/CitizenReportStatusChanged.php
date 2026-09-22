<?php

namespace App\Notifications;

use App\Models\CitizenReport;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CitizenReportStatusChanged extends Notification
{
    use Queueable;

    public function __construct(
        private readonly CitizenReport $report,
        private readonly string $status,
        private readonly ?string $description = null,
    ) {}

    public function via(object $notifiable): array
    {
        // Database is the source of truth. Broadcast is deliberately not enabled
        // until a server-side broadcast transport (Reverb/Pusher) is configured.
        return ['database'];
    }

    /** @return array<string, mixed> */
    public function toDatabase(object $notifiable): array
    {
        [$title, $message] = match ($this->status) {
            'submitted' => ['Laporan diterima', 'Laporan Anda telah diterima oleh sistem SIGMA.'],
            'verified' => ['Laporan diverifikasi', 'Laporan Anda telah diverifikasi.'],
            'process' => ['Laporan sedang ditangani', 'Laporan Anda sedang ditangani oleh petugas.'],
            'completed' => ['Laporan selesai', 'Laporan Anda telah selesai ditangani.'],
            'rejected' => ['Laporan tidak dapat diverifikasi', 'Laporan Anda tidak dapat diverifikasi.'],
            default => ['Laporan diperbarui', 'Status laporan Anda telah diperbarui.'],
        };

        return [
            'title' => $title,
            'message' => $this->description ?: $message,
            'type' => 'citizen_report_status',
            'report_id' => $this->report->id,
            'report_number' => 'RPT-'.str_pad((string) $this->report->id, 6, '0', STR_PAD_LEFT),
            'status' => $this->status,
        ];
    }
}
