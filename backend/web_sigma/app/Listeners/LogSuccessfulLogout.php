<?php

namespace App\Listeners;

use App\Models\User;
use Illuminate\Auth\Events\Logout;

class LogSuccessfulLogout
{
    /**
     * Handle the event.
     */
    public function handle(Logout $event): void
    {
        if (!$event->user instanceof User) {
            return;
        }

        activity()
            ->causedBy($event->user)
            ->useLog('authentication')
            ->withProperties([
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ])
            ->log('Logout dari sistem');
    }
}