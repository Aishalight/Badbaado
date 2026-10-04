<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\NotificationResource;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class NotificationController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return NotificationResource::collection(
            request()->user()->notifications()->latest()->paginate(50)
        );
    }

    /**
     * Unread notifications newer than the given id, oldest first.
     *
     * The alarm poller only needs rows it has never seen, so it asks for
     * everything after the highest id it already holds. Staying unread keeps a
     * dismissed notification from re-alarming on every subsequent poll.
     */
    public function pending(Request $request): AnonymousResourceCollection
    {
        $after = max(0, (int) $request->integer('after'));

        $notifications = request()->user()
            ->notifications()
            ->where('id', '>', $after)
            ->whereNull('read_at')
            ->orderBy('id')
            ->limit(20)
            ->get();

        return NotificationResource::collection($notifications);
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
