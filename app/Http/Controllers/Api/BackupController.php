<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Backup;
use App\Services\BackupService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BackupController extends Controller
{
    public function __construct(private readonly BackupService $backups) {}

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Backup::class);

        try {
            $backup = $this->backups->create($request->user(), $request->input('notes') ? [$request->input('notes')] : []);

            return response()->json([
                'message' => 'Backup created.',
                'backup' => [
                    'id' => $backup->id,
                    'filename' => $backup->filename,
                    'size' => $backup->size,
                    'status' => $backup->status,
                ],
            ], 201);
        } catch (\Throwable $exception) {
            return response()->json(['message' => 'Backup failed: '.$exception->getMessage()], 500);
        }
    }

    public function destroy(Backup $backup): JsonResponse
    {
        $this->authorize('delete', $backup);

        $this->backups->delete(request()->user(), $backup);

        return response()->json(['message' => 'Backup deleted.']);
    }
}
