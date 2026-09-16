<?php

namespace Database\Factories;

use App\Models\Notification;
use App\Models\Referral;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Notification>
 */
class NotificationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'referral_id' => Referral::factory(),
            'type' => fake()->randomElement(['referral_received', 'pre_alert', 'referral_accepted', 'referral_rejected']),
            'title' => fake()->sentence(4),
            'body' => fake()->paragraph(),
        ];
    }
}
