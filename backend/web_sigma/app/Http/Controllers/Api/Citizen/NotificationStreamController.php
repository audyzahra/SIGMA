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
            $latestId = $user->notifications()->latest()->value('id');
            $endsAt = now()->addSeconds(25);

            echo "event: notification\n";
            echo 'data: '.json_encode(['id' => $latestId], JSON_THROW_ON_ERROR)."\n\n";
            @ob_flush();
            flush();

            while (! connection_aborted() && now()->lessThan($endsAt)) {
                $newest = $user->notifications()->latest()->value('id');
                if ($newest && $newest !== $latestId) {
                    echo "event: notification\n";
                    echo 'data: '.json_encode(['id' => $newest], JSON_THROW_ON_ERROR)."\n\n";
                    $latestId = $newest;
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
