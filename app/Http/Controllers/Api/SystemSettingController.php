<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UpdateSystemSettingsRequest;
use App\Models\SystemSetting;
use App\Services\AuditLogger;
use App\Services\SettingsService;
use App\Support\AuditActions;
use Illuminate\Http\JsonResponse;

class SystemSettingController extends Controller
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
        private readonly SettingsService $settings,
    ) {}

    public function update(UpdateSystemSettingsRequest $request): JsonResponse
    {
        $changes = [];

        foreach ($request->validated('settings') as $key => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            $definition = $this->settings->definition((string) $key);
            $this->settings->set((string) $key, $value, $definition['type'] ?? null);
            $changes[] = (string) $key;
        }

        $this->auditLogger->record($request->user(), AuditActions::SETTINGS_UPDATED, SystemSetting::firstOrNew(['key' => 'platform.name']), [
            'keys' => $changes,
        ]);

        return response()->json(['message' => 'Settings saved.', 'updated' => $changes]);
    }
}
