<?php

namespace App\Http\Controllers;

use App\Events\StoreNotificationEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index() {
        $user = Auth::user();
        return response()->json(["data" => $user->notifications()->orderBy('created_at', 'desc')->get()], 200);
    }

    public function updateStatusRead() {
        $user = Auth::user();
        $notification = $user->notifications()->where('read', false)->get();
        foreach ($notification as $notify) {
            $notify->read = true;
            $notify->save();
        }
    }

    public function deleteNotifications() {
        $user = Auth::user();
        $notification = $user->notifications()->get();
        foreach ($notification as $notify) {
            $notify->delete();
        }
        return response()->json([
            "data" => [
                "message" => "Уведомления успешно удалены"
            ]
        ], 200);
    }
}
