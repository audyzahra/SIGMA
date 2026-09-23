<?php

namespace App\Notifications;

use App\Models\FieldAssignment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class OfficerTaskUpdated extends Notification
{
    use Queueable;

    public function __construct(
        private readonly FieldAssignment $task,
        private readonly string $event,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /** @return array<string, mixed> */
    public function toDatabase(object $notifiable): array
    {
        $location = $this->task->incident->location_description;

        return match ($this->event) {
            'accepted' => [
                'title' => 'Tugas diterima',
                'message' => 'Petugas telah menerima tugas untuk '.$location.'.',
                'type' => 'officer_task_accepted',
                'task_id' => $this->task->id,
            ],
            default => [
                'title' => 'Status tugas diperbarui',
                'message' => 'Status tugas '.$location.' berubah menjadi '.$this->task->status.'.',
                'type' => 'officer_task_status',
                'task_id' => $this->task->id,
                'status' => $this->task->status,
            ],
        };
    }
}
