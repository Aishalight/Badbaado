<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UpdateCmsContentRequest;
use App\Models\CmsContent;
use App\Services\AuditLogger;
use App\Services\CmsService;
use App\Support\AuditActions;
use Illuminate\Http\JsonResponse;

class CmsContentController extends Controller
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
        private readonly CmsService $cms,
    ) {}

    public function index(): JsonResponse
    {
        $this->authorize('viewAny', CmsContent::class);

        $contents = CmsContent::orderBy('section')->orderBy('key')->get();
        $definitions = collect($this->cms->definitions())->map(fn (array $definition, string $key) => [...$definition, 'key' => $key]);

        return response()->json([
            'contents' => $contents,
            'definitions' => $definitions,
        ]);
    }

    public function update(UpdateCmsContentRequest $request, CmsContent $content): JsonResponse
    {
        $this->authorize('update', $content);

        $content->update($request->validated());

        $this->auditLogger->record($request->user(), AuditActions::CMS_UPDATED, $content, [
            'key' => $content->key,
            'fields' => array_keys($request->validated()),
        ]);

        return response()->json(['message' => 'Content updated.']);
    }
}
