<?php

namespace App\Http\Controllers\Api\Officer;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'notifications' => $request->user()->notifications()->latest()->limit(50)->get()
                ->map(fn ($notification): array => $this->present($notification))->values(),
            'unread_count' => $request->user()->unreadNotifications()->count(),
        ]);
    }

    public function markRead(Request $request, string $notification): JsonResponse
    {
        $item = $request->user()->notifications()->findOrFail($notification);
        if (! $item->read_at) {
            $item->markAsRead();
        }

        return response()->json([
            'notification' => $this->present($item->fresh()),
            'unread_count' => $request->user()->unreadNotifications()->count(),
        ]);
    }

    /** @return array<string, mixed> */
    private function present(object $notification): array
    {
        return [
            'id' => $notification->id,
            'title' => $notification->data['title'] ?? 'Notifikasi SIGMA',
            'message' => $notification->data['message'] ?? '',
            'type' => $notification->data['type'] ?? null,
            'task_id' => isset($notification->data['task_id']) ? (string) $notification->data['task_id'] : null,
            'read_at' => $notification->read_at?->toIso8601String(),
            'created_at' => $notification->created_at?->toIso8601String(),
        ];
    }
}
