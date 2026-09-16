<?php

namespace Database\Factories;

use App\Models\Hospital;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Hospital>
 */
class HospitalFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->company(),
            'short_name' => strtoupper(fake()->unique()->lexify('H??')),
            'code' => strtoupper(fake()->unique()->lexify('H????')),
            'location' => fake()->city().', '.fake()->state(),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->unique()->companyEmail(),
            'level' => fake()->randomElement(['primary', 'secondary', 'tertiary']),
            'is_active' => true,
        ];
    }
}
