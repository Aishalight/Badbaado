<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class NotificationPageController extends Controller
{
    public function __invoke(): View
    {
        $notifications = auth()->user()->notifications()
            ->with('referral:id,referral_number,status')
            ->orderByDesc('created_at')
            ->take(50)
            ->get();

        return view('notifications.index', [
            'notifications' => $notifications,
        ]);
    }
}
