<?php

namespace App\Http\Controllers\Api\Citizen;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Authenticated Server-Sent Events transport for foreground mobile sessions.
 * The database notification remains authoritative; this only signals clients
 * to fetch it immediately when a new row is committed.
 */
class NotificationStreamController extends Controller
{
    public function __invoke(Request $request): StreamedResponse
    {
        $user = $request->user();

        return response()->stream(function () use ($user): void {
            $notificationCount = $user->notifications()->count();
            $endsAt = now()->addSeconds(25);

            echo "event: notification\n";
            echo "data: {}\n\n";
            @ob_flush();
            flush();

            while (! connection_aborted() && now()->lessThan($endsAt)) {
                $newCount = $user->notifications()->count();
                if ($newCount > $notificationCount) {
                    echo "event: notification\n";
                    echo "data: {}\n\n";
                    $notificationCount = $newCount;
                    @ob_flush();
                    flush();
                }
                usleep(500000);
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache, no-transform',
            'X-Accel-Buffering' => 'no',
        ]);
    }
}
