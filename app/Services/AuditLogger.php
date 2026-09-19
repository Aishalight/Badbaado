<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;

class AuditLogger
{
    private const string ENTITY_MAP = 'App\Models\\';

    public function record(?User $user, string $action, ?Model $entity = null, array $metadata = []): AuditLog
    {
        return AuditLog::create([
            'user_id' => $user?->id,
            'action' => $action,
            'entity_type' => $entity ? str_replace(self::ENTITY_MAP, '', $entity::class) : null,
            'entity_id' => $entity?->getKey(),
            'metadata' => $metadata,
            'ip_address' => Request::ip(),
        ]);
    }
}
