<?php

namespace Database\Factories;

use App\Models\Backup;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Backup>
 */
class BackupFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'filename' => 'backup-'.now()->format('Ymd-His').'.zip',
            'disk' => 'backups',
            'size' => fake()->numberBetween(1_000, 5_000_000),
            'status' => 'completed',
            'notes' => null,
        ];
    }
}
