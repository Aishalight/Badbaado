<?php

namespace App\Services;

use App\Models\SystemSetting;
use Illuminate\Support\Collection;

class SettingsService
{
    private const string CACHE_KEY = 'badbaado.system_settings';

    /**
     * Defaults power the platform until a System Administrator persists an override.
     *
     * @return array<string, array{value: mixed, type: string, group: string, label: string, description: string, is_protected: bool}>
     */
    public function defaults(): array
    {
        return [
            'platform.name' => ['value' => 'BADBAADO', 'type' => 'string', 'group' => 'Platform', 'label' => 'Platform name', 'description' => 'Public-facing platform name.', 'is_protected' => true],
            'platform.tagline' => ['value' => 'Connecting Hospitals, Connecting Care', 'type' => 'string', 'group' => 'Platform', 'label' => 'Tagline', 'description' => 'Displayed in public headings and the console masthead.', 'is_protected' => false],
            'platform.maintenance_mode' => ['value' => false, 'type' => 'boolean', 'group' => 'Platform', 'label' => 'Maintenance mode', 'description' => 'When enabled, non-administrators see a maintenance notice instead of the console.', 'is_protected' => false],
            'contact.support_email' => ['value' => null, 'type' => 'string', 'group' => 'Contact', 'label' => 'Support email', 'description' => 'Public contact / support email address.', 'is_protected' => false],
            'contact.support_phone' => ['value' => null, 'type' => 'string', 'group' => 'Contact', 'label' => 'Support phone', 'description' => 'Public contact / support phone number.', 'is_protected' => false],
            'contact.address' => ['value' => null, 'type' => 'string', 'group' => 'Contact', 'label' => 'Operations address', 'description' => 'Public operational address.', 'is_protected' => false],
            'auth.registration_enabled' => ['value' => true, 'type' => 'boolean', 'group' => 'Access & security', 'label' => 'Open registration', 'description' => 'When disabled, the public registration endpoint refuses new accounts.', 'is_protected' => false],
            'auth.session_timeout_minutes' => ['value' => 720, 'type' => 'integer', 'group' => 'Access & security', 'label' => 'Session timeout (minutes)', 'description' => 'Idle session lifetime used by the platform.', 'is_protected' => false],
            'backups.retention_days' => ['value' => 30, 'type' => 'integer', 'group' => 'Operations', 'label' => 'Backup retention (days)', 'description' => 'Archives older than this are pruned automatically.', 'is_protected' => false],
        ];
    }

    public function definition(string $key): ?array
    {
        return $this->defaults()[$key] ?? null;
    }

    /**
     * Read a setting with a typed default.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $setting = $this->all()->firstWhere('key', $key);

        if ($setting !== null) {
            return $setting->typedValue();
        }

        $definition = $this->definition($key);

        return $definition ? $definition['value'] : $default;
    }

    public function getString(string $key, ?string $default = null): ?string
    {
        $value = $this->get($key, $default);

        return $value === null ? null : (string) $value;
    }

    /**
     * Persist a single setting, creating it from its definition when it does not exist yet.
     */
    public function set(string $key, mixed $value, ?string $type = null): void
    {
        $definition = $this->definition($key);
        $type ??= $definition['type'] ?? 'string';

        SystemSetting::updateOrCreate(
            ['key' => $key],
            [
                'value' => $this->serialize($value, $type),
                'type' => $type,
                'group' => $definition['group'] ?? 'General',
                'label' => $definition['label'] ?? ucfirst(str_replace(['.', '_'], ' ', $key)),
                'description' => $definition['description'] ?? null,
                'is_protected' => $definition['is_protected'] ?? false,
            ]
        );

        cache()->forget(self::CACHE_KEY);
    }

    /**
     * @return Collection<int, SystemSetting>
     */
    public function all(): Collection
    {
        $cached = cache()->get(self::CACHE_KEY);

        if ($cached instanceof Collection) {
            return $cached;
        }

        $settings = SystemSetting::all();

        cache()->put(self::CACHE_KEY, $settings, now()->addHour());

        return $settings;
    }

    public function group(string $group): Collection
    {
        return $this->all()->where('group', $group)->values();
    }

    public function groups(): Collection
    {
        $settings = $this->all()->keyBy('key');

        foreach ($this->defaults() as $key => $definition) {
            if ($settings->has($key)) {
                continue;
            }

            $setting = new SystemSetting([
                'key' => $key,
                'value' => $this->serialize($definition['value'], $definition['type']),
                'type' => $definition['type'],
                'group' => $definition['group'],
                'label' => $definition['label'],
                'description' => $definition['description'],
                'is_protected' => $definition['is_protected'],
            ]);
            $setting->exists = true;

            $settings->put($key, $setting);
        }

        return $settings
            ->groupBy('group')
            ->mapWithKeys(fn (Collection $items, string $group) => [$group => $items->sortBy('label')->values()]);
    }

    public function maintenanceEnabled(): bool
    {
        return (bool) $this->get('platform.maintenance_mode', false);
    }

    private function serialize(mixed $value, string $type): string
    {
        return match ($type) {
            'boolean' => $value ? '1' : '0',
            'array' => json_encode($value) ?: '[]',
            default => (string) $value,
        };
    }
}
