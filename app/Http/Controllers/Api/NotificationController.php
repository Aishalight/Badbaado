<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\NotificationResource;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class NotificationController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return NotificationResource::collection(
            request()->user()->notifications()->latest()->paginate(50)
        );
    }

    public function markAsRead(Notification $notification): JsonResponse
    {
        abort_unless($notification->user_id === request()->user()->id, 403);

        $notification->update(['read_at' => now()]);

        return response()->json(['message' => 'Notification marked as read.']);
    }

    public function markAllAsRead(): JsonResponse
    {
        request()->user()->notifications()->whereNull('read_at')->update(['read_at' => now()]);

        return response()->json(['message' => 'All notifications marked as read.']);
    }
}
