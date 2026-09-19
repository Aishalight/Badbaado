<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StorePlatformAlertRequest;
use App\Models\Notification;
use App\Models\Role;
use App\Models\User;
use App\Services\AuditLogger;
use App\Support\AuditActions;
use Illuminate\Http\JsonResponse;

class PlatformAlertController extends Controller
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function store(StorePlatformAlertRequest $request): JsonResponse
    {
        $audience = $request->validated('audience');

        $roleId = $audience === 'all' ? null : Role::where('slug', $audience)->value('id');

        $users = User::query()
            ->where('is_active', true)
            ->when($roleId, fn ($query, int $id) => $query->where('role_id', $id))
            ->pluck('id');

        $payload = [
            'type' => 'platform_alert',
            'title' => $request->validated('title'),
            'body' => $request->validated('body'),
            'created_at' => now(),
            'updated_at' => now(),
        ];

        $rows = $users->map(fn (int $userId) => ['user_id' => $userId] + $payload)->all();

        Notification::insert($rows);

        $this->auditLogger->record($request->user(), AuditActions::PLATFORM_ALERT_SENT, Notification::latest('id')->first() ?? Notification::make(), [
            'audience' => $audience,
            'recipients' => count($rows),
            'title' => $request->validated('title'),
        ]);

        return response()->json([
            'message' => 'Alert broadcast to '.count($rows).' user'.(count($rows) === 1 ? '' : 's').'.',
            'recipients' => count($rows),
        ], 201);
    }
}
