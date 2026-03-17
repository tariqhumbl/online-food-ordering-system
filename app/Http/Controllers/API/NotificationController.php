<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Notification as UserNotification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $perPage = (int) $request->get('per_page', 20);
        $notifications = UserNotification::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->paginate($perPage);
        $unreadCount = UserNotification::where('user_id', $user->id)->unread()->count();
        return response()->json([
            'notifications' => $notifications->items(),
            'unread_count' => $unreadCount,
            'total' => $notifications->total(),
        ]);
    }

    public function markAsRead(Request $request)
    {
        $user = $request->user();
        $id = $request->input('id');
        if ($id) {
            UserNotification::where('user_id', $user->id)->where('id', $id)->update(['read_at' => now()]);
        } else {
            UserNotification::where('user_id', $user->id)->whereNull('read_at')->update(['read_at' => now()]);
        }
        $unreadCount = UserNotification::where('user_id', $user->id)->unread()->count();
        return response()->json(['unread_count' => $unreadCount]);
    }
}
