<?php

namespace Database\Factories;

use App\Models\CmsContent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CmsContent>
 */
class CmsContentFactory extends Factory
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
            'section' => 'hero',
            'title' => fake()->words(3, true),
            'content' => fake()->paragraph(),
            'is_active' => true,
        ];
    }
}
