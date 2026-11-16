<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * 通知一覧
     */
    public function index(Request $request)
    {
        $notifications = $request->user()
            ->notifications()
            ->latest()
            ->paginate(10);

        return view('notifications.index', compact('notifications'));
    }

    /**
     * 通知を既読にする
     */
    public function markAsRead(Request $request, string $id)
    {
        // ログインユーザー自身の通知だけを取得
        $notification = $request->user()
            ->notifications()
            ->where('id', $id)
            ->firstOrFail();

        // 通知を既読にする
        $notification->markAsRead();

        return redirect()
            ->route('notifications.index')
            ->with('success', '通知を既読にしました。');
    }
}
