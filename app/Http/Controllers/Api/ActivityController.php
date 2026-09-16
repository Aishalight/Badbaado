<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\AuditLogResource;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ActivityController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $logs = AuditLog::query()
            ->when($request->string('entity')->toString(), fn ($query, $entity) => $query->where('entity_type', $entity))
            ->with('user:id,name,title')
            ->orderByDesc('created_at')
            ->paginate(50);

        return AuditLogResource::collection($logs);
    }
}
