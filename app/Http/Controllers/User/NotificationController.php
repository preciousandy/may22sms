<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = auth()->user()
            ->notifications()
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('user.notifications', compact('notifications'));
    }

    public function markRead(Request $request)
    {
        $id = (string) $request->input('id', '');
        if ($id !== '') {
            $n = auth()->user()->notifications()->where('id', $id)->first();
            if ($n && $n->read_at === null) {
                $n->markAsRead();
            }
        }

        return back();
    }

    public function markAllRead()
    {
        auth()->user()->unreadNotifications->markAsRead();

        return back();
    }
}
