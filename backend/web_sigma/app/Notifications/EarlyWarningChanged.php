<?php

namespace App\Notifications;

use App\Models\EarlyWarning;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class EarlyWarningChanged extends Notification
{
    use Queueable;

    public function __construct(private readonly EarlyWarning $warning, private readonly string $action) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /** @return array<string, mixed> */
    public function toDatabase(object $notifiable): array
    {
        $region = collect([$this->warning->region?->name, $this->warning->region?->parent?->name, $this->warning->region?->parent?->parent?->name])->filter()->implode(', ');
        $message = match ($this->action) {
            'issued' => 'Peringatan baru diterbitkan untuk '.$this->warning->area.'. '.$this->warning->reason,
            'closed' => 'Peringatan untuk '.$this->warning->area.' telah ditutup.',
            default => 'Peringatan untuk '.$this->warning->area.' telah diperbarui. '.$this->warning->reason,
        };

        return [
            'title' => 'Peringatan Karhutla'.($region ? ' di '.$region : ''),
            'message' => $message,
            'type' => 'early_warning',
            'warning_id' => $this->warning->id,
            'warning_level' => $this->warning->level,
            'warning_status' => $this->warning->status,
            'status' => $this->warning->status,
            'warning_area' => $this->warning->area,
            'warning_region' => $region,
            'warning_reason' => $this->warning->reason,
            'warning_instructions' => $this->warning->instructions,
            'warning_issued_at' => $this->warning->issued_at?->toIso8601String(),
            'warning_expires_at' => $this->warning->expires_at?->toIso8601String(),
            'action' => $this->action,
        ];
    }
}
