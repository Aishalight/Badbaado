<?php

namespace Database\Factories;

use App\Enums\ReferralStatus;
use App\Models\Hospital;
use App\Models\Patient;
use App\Models\Referral;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Referral>
 */
class ReferralFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $referringHospital = Hospital::inRandomOrder()->first() ?? Hospital::factory()->create();
        $receivingHospital = Hospital::where('id', '!=', $referringHospital->id)->inRandomOrder()->first()
            ?? Hospital::factory()->create();

        return [
            'referral_number' => 'REF-'.fake()->unique()->numberBetween(1000, 999999),
            'referring_hospital_id' => $referringHospital->id,
            'receiving_hospital_id' => $receivingHospital->id,
            'referring_user_id' => User::factory(),
            'patient_id' => Patient::factory(),
            'status' => ReferralStatus::DRAFT,
            'department' => fake()->randomElement(['Emergency', 'Cardiology', 'Neurology', 'Pediatrics', 'Orthopedics', 'General Surgery']),
            'referral_reason' => fake()->sentence(),
            'symptoms' => fake()->paragraph(),
            'trauma_indicator' => fake()->boolean(),
            'is_emergency' => fake()->boolean(),
        ];
    }
}
