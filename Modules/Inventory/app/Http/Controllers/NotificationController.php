<?php

namespace Modules\Inventory\App\Http\Controllers;

use Modules\Inventory\App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    // ✔ single notification mark as read
    public function markAsRead($id = null)
    {
        $notification = Notification::findOrFail($id);

        $user = Auth::user();

        if (!$user) {
            return back();
        }
        if ( $notification->notify_id == $user->id || $notification->role_id == $user->role_id ) {
            $notification->update([
                'read_at' => 1
            ]);
        }

        return back();
    }

    public function destroy($id)
    {
        $notification = Notification::findOrFail($id);
        $user = Auth::user();

        if ( $notification->notify_id == $user->id || $notification->role_id == $user->role_id ) {
             $notification->delete();
        }

        return back();
    }

    public function markAllAsRead()
    {
        $user = Auth::user();

        Notification::where(function ($query) use ($user) {
                $query->where('notify_id', $user->id)
                      ->orWhere('role_id', $user->role_id);
            })
            ->where('read_at', 0)
            ->update(['read_at' => 1]);

        return back();
    }
}
