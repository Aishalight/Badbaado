<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreAnnouncementRequest;
use App\Http\Requests\Api\UpdateAnnouncementRequest;
use App\Models\Announcement;
use App\Services\AuditLogger;
use App\Support\AuditActions;
use Illuminate\Http\JsonResponse;

class AnnouncementController extends Controller
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function index(): JsonResponse
    {
        $this->authorize('viewAny', Announcement::class);

        return response()->json(Announcement::query()
            ->with('author:id,name')
            ->orderByDesc('published_at')
            ->get());
    }

    public function store(StoreAnnouncementRequest $request): JsonResponse
    {
        $this->authorize('create', Announcement::class);

        $announcement = Announcement::create([
            'title' => $request->validated('title'),
            'body' => $request->validated('body'),
            'author_id' => $request->user()->id,
            'published_at' => $request->filled('published_at') ? $request->date('published_at') : now(),
            'is_active' => $request->boolean('is_active', true),
        ]);

        $this->auditLogger->record($request->user(), AuditActions::ANNOUNCEMENT_CREATED, $announcement, [
            'published_at' => $announcement->published_at?->toDateTimeString(),
        ]);

        return response()->json(['message' => 'Announcement created.', 'id' => $announcement->id], 201);
    }

    public function update(UpdateAnnouncementRequest $request, Announcement $announcement): JsonResponse
    {
        $this->authorize('update', $announcement);

        $announcement->update($request->validated());

        $this->auditLogger->record($request->user(), AuditActions::ANNOUNCEMENT_UPDATED, $announcement, [
            'fields' => array_keys($request->validated()),
        ]);

        return response()->json(['message' => 'Announcement updated.']);
    }

    public function destroy(Announcement $announcement): JsonResponse
    {
        $this->authorize('delete', $announcement);

        $this->auditLogger->record(request()->user(), AuditActions::ANNOUNCEMENT_DELETED, $announcement, [
            'title' => $announcement->title,
        ]);
        $announcement->delete();

        return response()->json(['message' => 'Announcement deleted.']);
    }
}
