<?php

namespace Database\Factories;

use App\Models\SystemSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SystemSetting>
 */
class SystemSettingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'key' => fake()->slug(2),
            'value' => fake()->sentence(),
            'type' => 'string',
            'group' => 'general',
            'label' => fake()->words(3, true),
            'description' => fake()->sentence(),
            'is_protected' => false,
        ];
    }
}
