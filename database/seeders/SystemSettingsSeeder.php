<?php

namespace Database\Seeders;

use App\Models\SystemSetting;
use Illuminate\Database\Seeder;

class SystemSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaults = [
            ['key' => 'platform.name', 'value' => config('app.name', 'BADBAADO'), 'type' => 'string', 'group' => 'Platform', 'label' => 'Platform name', 'description' => 'Public-facing platform name.', 'is_protected' => true],
            ['key' => 'platform.tagline', 'value' => 'Connecting Hospitals, Connecting Care', 'type' => 'string', 'group' => 'Platform', 'label' => 'Tagline', 'description' => 'Displayed in public headings and the console masthead.', 'is_protected' => false],
            ['key' => 'contact.support_email', 'value' => null, 'type' => 'string', 'group' => 'Contact', 'label' => 'Support email', 'description' => 'Public contact / support email address.', 'is_protected' => false],
            ['key' => 'contact.support_phone', 'value' => null, 'type' => 'string', 'group' => 'Contact', 'label' => 'Support phone', 'description' => 'Public contact / support phone number.', 'is_protected' => false],
            ['key' => 'contact.address', 'value' => null, 'type' => 'string', 'group' => 'Contact', 'label' => 'Operations address', 'description' => 'Public operational address.', 'is_protected' => false],
            ['key' => 'auth.registration_enabled', 'value' => '1', 'type' => 'boolean', 'group' => 'Access & security', 'label' => 'Open registration', 'description' => 'When disabled, public registration is refused.', 'is_protected' => false],
            ['key' => 'auth.session_timeout_minutes', 'value' => '720', 'type' => 'integer', 'group' => 'Access & security', 'label' => 'Session timeout (minutes)', 'description' => 'Idle session lifetime used by the platform.', 'is_protected' => false],
            ['key' => 'backups.retention_days', 'value' => '30', 'type' => 'integer', 'group' => 'Operations', 'label' => 'Backup retention (days)', 'description' => 'Archives older than this are pruned automatically.', 'is_protected' => false],
        ];

        foreach ($defaults as $seed) {
            SystemSetting::firstOrCreate(['key' => $seed['key']], $seed);
        }

        $this->command?->info('System settings seeded.');
    }
}
