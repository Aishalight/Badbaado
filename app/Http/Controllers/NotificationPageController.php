<?php

namespace App\Http\Controllers;

use App\Enums\NotificationSeverity;
use Illuminate\View\View;

class NotificationPageController extends Controller
{
    public function __invoke(): View
    {
        $user = auth()->user();

        $notifications = $user->notifications()
            ->with('referral:id,referral_number,status')
            ->orderByDesc('created_at')
            ->take(50)
            ->get();

        return view('notifications.index', [
            'notifications' => $notifications,
            'unreadTotal' => $user->notifications()->unread()->count(),
            'unreadAlarms' => $user->notifications()->unread()->interrupting()->count(),
            'unreadCritical' => $user->notifications()
                ->unread()
                ->where('severity', NotificationSeverity::CRITICAL->value)
                ->count(),
        ]);
    }
}
