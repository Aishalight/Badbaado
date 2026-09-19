<?php

namespace App\Console\Commands;

use App\Services\BackupService;
use App\Services\SettingsService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('backups:create {--notes= : Optional note attached to the backup record}')]
#[Description('Create a database and uploads archive and prune expired backups')]
class CreateBackup extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(BackupService $backups, SettingsService $settings): int
    {
        $this->components->info('Starting platform backup...');

        try {
            $backup = $backups->create(null, $this->option('notes') ? [$this->option('notes')] : []);
            $this->components->info("Backup completed: {$backup->filename} (".number_format(($backup->size ?? 0) / 1024 / 1024, 2).' MB)');
        } catch (\Throwable $exception) {
            $this->components->error('Backup failed: '.$exception->getMessage());

            return self::FAILURE;
        }

        $retention = (int) $settings->get('backups.retention_days', 30);
        $pruned = $backups->prune(null, $retention, $backup);

        if ($pruned->isEmpty()) {
            $this->components->info("No backups older than {$retention} days to prune.");
        } else {
            $this->components->info('Pruned '.$pruned->count().' expired backup(s).');
        }

        return self::SUCCESS;
    }
}
