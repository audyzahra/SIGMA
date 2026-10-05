<?php

namespace App\Http\Controllers\Web\Government;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Halaman semua notifikasi
     */
    public function index()
    {
        $notifications = auth()
            ->user()
            ->notifications()
            ->latest()
            ->paginate(10);


        return view(
            'government.notifications.index',
            compact('notifications')
        );
    }


    /**
     * Tandai satu notifikasi sudah dibaca
     */
    public function read($id)
    {
        $notification = auth()
            ->user()
            ->notifications()
            ->where('id', $id)
            ->firstOrFail();


        $notification->markAsRead();


        return redirect()
            ->back();
    }


    /**
     * Tandai semua sudah dibaca
     */
    public function readAll()
    {
        auth()
            ->user()
            ->unreadNotifications
            ->markAsRead();


        return redirect()
            ->back();
    }
}