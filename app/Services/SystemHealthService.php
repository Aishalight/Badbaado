<?php

namespace App\Services;

use App\Models\Backup;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SystemHealthService
{
    /**
     * @return array<string, array<int, array{key: string, label: string, status: string, detail: string}>>
     */
    public function checks(): array
    {
        return [
            'Application' => [
                $this->appEnvironment(),
                $this->debugMode(),
                $this->phpRuntime(),
            ],
            'Data' => [
                $this->databaseConnection(),
                $this->cacheStore(),
                $this->migrations(),
                $this->lastBackup(),
            ],
            'Storage' => [
                $this->diskWritable('local'),
                $this->diskWritable('backups'),
                $this->publicLink(),
                $this->diskSpace(),
            ],
            'Queues' => [
                $this->queueConnection(),
                $this->failedJobs(),
            ],
        ];
    }

    /**
     * @return array{key: string, label: string, status: string, detail: string}
     */
    private function appEnvironment(): array
    {
        $env = app()->environment();

        return [
            'key' => 'environment',
            'label' => 'Environment',
            'status' => in_array($env, ['local', 'testing'], true) ? 'ok' : 'ok',
            'detail' => ucfirst($env).' · '.config('app.name').' v'.app()->version(),
        ];
    }

    /**
     * @return array{key: string, label: string, status: string, detail: string}
     */
    private function debugMode(): array
    {
        $debug = (bool) config('app.debug');

        return [
            'key' => 'debug',
            'label' => 'Debug mode',
            'status' => $debug && app()->isProduction() ? 'warn' : 'ok',
            'detail' => $debug ? 'Enabled — do not leave on in production' : 'Disabled',
        ];
    }

    /**
     * @return array{key: string, label: string, status: string, detail: string}
     */
    private function phpRuntime(): array
    {
        $memory = ini_get('memory_limit') ?: 'unknown';
        $extension = class_exists('ZipArchive') ? 'zip' : null;

        return [
            'key' => 'php',
            'label' => 'PHP runtime',
            'status' => 'ok',
            'detail' => 'PHP '.PHP_VERSION.' · memory '.$memory.($extension ? ' · zip available' : ''),
        ];
    }

    /**
     * @return array{key: string, label: string, status: string, detail: string}
     */
    private function databaseConnection(): array
    {
        try {
            DB::connection()->getPdo();
            DB::select('SELECT 1');

            return [
                'key' => 'database',
                'label' => 'Database connection',
                'status' => 'ok',
                'detail' => 'Responding · '.config('database.default').' driver',
            ];
        } catch (\Throwable $exception) {
            return [
                'key' => 'database',
                'label' => 'Database connection',
                'status' => 'fail',
                'detail' => $exception->getMessage(),
            ];
        }
    }

    /**
     * @return array{key: string, label: string, status: string, detail: string}
     */
    private function cacheStore(): array
    {
        try {
            Cache::put('badbaado.health_check', 'ok', now()->addMinute());
            $ok = Cache::get('badbaado.health_check') === 'ok';

            return [
                'key' => 'cache',
                'label' => 'Cache store',
                'status' => $ok ? 'ok' : 'fail',
                'detail' => ucfirst(config('cache.default')).' driver · '.($ok ? 'read/write ok' : 'read/write failed'),
            ];
        } catch (\Throwable $exception) {
            return [
                'key' => 'cache',
                'label' => 'Cache store',
                'status' => 'fail',
                'detail' => $exception->getMessage(),
            ];
        }
    }

    /**
     * @return array{key: string, label: string, status: string, detail: string}
     */
    private function migrations(): array
    {
        try {
            Artisan::call('migrate:status', ['--no-interaction' => true]);
            $output = Artisan::output();
            $pending = Str::of($output)->contains('Pending');

            if ($pending) {
                return [
                    'key' => 'migrations',
                    'label' => 'Migrations',
                    'status' => 'warn',
                    'detail' => 'Some migrations have not been applied.',
                ];
            }

            return [
                'key' => 'migrations',
                'label' => 'Migrations',
                'status' => 'ok',
                'detail' => 'All migrations are applied.',
            ];
        } catch (\Throwable $exception) {
            return [
                'key' => 'migrations',
                'label' => 'Migrations',
                'status' => 'fail',
                'detail' => $exception->getMessage(),
            ];
        }
    }

    /**
     * @return array{key: string, label: string, status: string, detail: string}
     */
    private function lastBackup(): array
    {
        $latest = Backup::query()->where('status', 'completed')->latest()->first();

        $detail = $latest
            ? 'Last completed '.$latest->created_at->diffForHumans().' · '.$latest->filename
            : 'No completed backup recorded yet.';

        $status = $latest?->created_at->diffInHours(now()) <= 48 ? 'ok' : 'warn';

        return [
            'key' => 'backup',
            'label' => 'Latest backup',
            'status' => $status,
            'detail' => $detail,
        ];
    }

    /**
     * @return array{key: string, label: string, status: string, detail: string}
     */
    private function diskWritable(string $disk): array
    {
        $probe = 'health-'.Str::random(8).'.tmp';

        try {
            Storage::disk($disk)->put($probe, 'ok');
            $readable = Storage::disk($disk)->get($probe) === 'ok';
            Storage::disk($disk)->delete($probe);

            return [
                'key' => 'disk_'.$disk,
                'label' => ucfirst($disk).' disk',
                'status' => $readable ? 'ok' : 'fail',
                'detail' => 'Writable · '.storage_path('app'.DIRECTORY_SEPARATOR.$disk),
            ];
        } catch (\Throwable $exception) {
            return [
                'key' => 'disk_'.$disk,
                'label' => ucfirst($disk).' disk',
                'status' => 'fail',
                'detail' => $exception->getMessage(),
            ];
        }
    }

    /**
     * @return array{key: string, label: string, status: string, detail: string}
     */
    private function publicLink(): array
    {
        $link = public_path('storage');

        if (is_link($link)) {
            return [
                'key' => 'storage_link',
                'label' => 'Public storage link',
                'status' => 'ok',
                'detail' => 'Symlink present',
            ];
        }

        return [
            'key' => 'storage_link',
            'label' => 'Public storage link',
            'status' => 'warn',
            'detail' => 'Run php artisan storage:link so avatars resolve.',
        ];
    }

    /**
     * @return array{key: string, label: string, status: string, detail: string}
     */
    private function diskSpace(): array
    {
        $free = disk_free_space(storage_path());
        $total = disk_total_space(storage_path());

        if ($free === false || $total === false) {
            return [
                'key' => 'disk_space',
                'label' => 'Disk space',
                'status' => 'warn',
                'detail' => 'Unable to determine free space.',
            ];
        }

        $freeMb = (int) round($free / 1024 / 1024);
        $percentUsed = (int) round((1 - $free / $total) * 100);
        $status = $percentUsed > 90 ? 'fail' : ($percentUsed > 75 ? 'warn' : 'ok');

        return [
            'key' => 'disk_space',
            'label' => 'Disk space',
            'status' => $status,
            'detail' => $freeMb.' MB free · '.$percentUsed.'% used',
        ];
    }

    /**
     * @return array{key: string, label: string, status: string, detail: string}
     */
    private function queueConnection(): array
    {
        $driver = config('queue.default');

        return [
            'key' => 'queue',
            'label' => 'Queue connection',
            'status' => 'ok',
            'detail' => ucfirst($driver).' driver'.($driver === 'sync' ? ' — jobs run synchronously' : ''),
        ];
    }

    /**
     * @return array{key: string, label: string, status: string, detail: string}
     */
    private function failedJobs(): array
    {
        try {
            $count = DB::table('failed_jobs')->count();

            return [
                'key' => 'failed_jobs',
                'label' => 'Failed jobs',
                'status' => $count === 0 ? 'ok' : 'warn',
                'detail' => $count === 0 ? 'None recorded' : $count.' failed job'.($count === 1 ? '' : 's'),
            ];
        } catch (\Throwable $exception) {
            return [
                'key' => 'failed_jobs',
                'label' => 'Failed jobs',
                'status' => 'warn',
                'detail' => 'Unable to read failed_jobs table.',
            ];
        }
    }
}
