<?php

namespace App\Services;

use App\Models\Backup;
use App\Models\User;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipArchive;

class BackupService
{
    /**
     * @param  array<string>  $excludedPaths  absolute paths never bundled into an archive
     */
    private array $excludedPaths = [];

    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function disk(): Filesystem
    {
        return Storage::disk('backups');
    }

    /**
     * @return Collection<int, Backup>
     */
    public function all(): Collection
    {
        return Backup::with('creator:id,name')->orderByDesc('created_at')->get();
    }

    public function create(?User $actor = null, array $notes = []): Backup
    {
        $filename = 'badbaado-'.now()->format('Y-m-d-His').'-'.Str::lower(Str::random(6)).'.zip';
        $tempPath = tempnam(sys_get_temp_dir(), 'badbaado_');

        if ($tempPath === false) {
            throw new RuntimeException('Unable to allocate a temporary file for the backup.');
        }

        try {
            $zip = new ZipArchive;

            if ($zip->open($tempPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                throw new RuntimeException('Unable to open the archive for writing.');
            }

            $zip->addFromString(
                'manifest.json',
                json_encode([
                    'platform' => config('app.name'),
                    'generated_at' => now()->toIso8601String(),
                    'app_version' => app()->version(),
                    'environment' => app()->environment(),
                    'entries' => [
                        'database' => $this->databaseFilePath(),
                        'private_storage' => storage_path('app/private'),
                        'public_storage' => storage_path('app/public'),
                    ],
                ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
            );

            $this->addDirectoryToZip($zip, storage_path('app/private'));
            $this->addDirectoryToZip($zip, storage_path('app/public'));
            $this->addFileToZip($zip, $this->databaseFilePath());

            $zip->close();

            $this->disk()->put($filename, file_get_contents($tempPath), 'private');
            $size = filesize($tempPath);

            /** @var Backup $backup */
            $backup = Backup::create([
                'filename' => $filename,
                'disk' => 'backups',
                'size' => $size,
                'status' => 'completed',
                'notes' => $notes ? implode("\n", $notes) : null,
                'created_by' => $actor?->id,
            ]);

            $this->auditLogger->record($actor, 'backup_created', $backup, [
                'filename' => $filename,
                'size' => $size,
            ]);

            return $backup;
        } catch (\Throwable $exception) {
            /** @var Backup $backup */
            $backup = Backup::create([
                'filename' => $filename,
                'disk' => 'backups',
                'size' => null,
                'status' => 'failed',
                'notes' => $exception->getMessage(),
                'created_by' => $actor?->id,
            ]);

            $this->auditLogger->record($actor, 'backup_failed', $backup, [
                'filename' => $filename,
                'message' => $exception->getMessage(),
            ]);

            throw $exception;
        } finally {
            @unlink($tempPath);
        }
    }

    /**
     * @param  Backup|null  $keepBackup  a specific archive to exempt from pruning
     * @return Collection<int, Backup> pruned backups
     */
    public function prune(?User $actor, int $retentionDays, ?Backup $keepBackup = null): Collection
    {
        $cutoff = now()->subDays(max(1, $retentionDays));

        $pruned = Backup::query()
            ->where('created_at', '<', $cutoff)
            ->when($keepBackup, fn ($query, Backup $keep) => $query->whereKeyNot($keep->getKey()))
            ->get();

        foreach ($pruned as $backup) {
            $this->delete($actor, $backup);
        }

        return $pruned;
    }

    public function delete(?User $actor, Backup $backup): void
    {
        if ($backup->filename && $this->disk()->exists($backup->filename)) {
            $this->disk()->delete($backup->filename);
        }

        $this->auditLogger->record($actor, 'backup_deleted', $backup, [
            'filename' => $backup->filename,
            'status' => $backup->status,
        ]);

        $backup->delete();
    }

    public function download(Backup $backup): StreamedResponse
    {
        abort_if(! $this->disk()->exists($backup->filename), 404, 'Backup archive is no longer available.');

        return $this->disk()->download($backup->filename, $backup->filename);
    }

    private function databaseFilePath(): string
    {
        $connection = config('database.default');
        $database = config("database.connections.{$connection}.database");

        if ($database === null) {
            throw new RuntimeException("The [{$connection}] connection has no database configured for backup.");
        }

        $database = (string) $database;

        $isAbsolute = Str::startsWith($database, DIRECTORY_SEPARATOR)
            || (bool) preg_match('/^[A-Za-z]:[\\\\\/]/', $database);

        return $isAbsolute ? $database : database_path($database);
    }

    private function addFileToZip(ZipArchive $zip, string $path, ?string $localPath = null): void
    {
        if (is_file($path) && is_readable($path)) {
            $zip->addFile($path, $localPath ?? 'database'.DIRECTORY_SEPARATOR.basename($path));
        }
    }

    private function addDirectoryToZip(ZipArchive $zip, string $directory): void
    {
        if (! is_dir($directory)) {
            return;
        }

        $prefix = Str::startsWith($directory, storage_path('app'))
            ? 'storage/'.basename($directory).'/'
            : basename($directory).'/';

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $file) {
            $relative = $prefix.Str::after($file->getPathname(), $directory);

            if (in_array($file->getPathname(), $this->excludedPaths, true)) {
                continue;
            }

            if ($file->isDir()) {
                $zip->addEmptyDir($relative);
            } elseif ($file->isFile() && is_readable($file->getPathname())) {
                $zip->addFile($file->getPathname(), $relative);
            }
        }
    }
}
