<?php

namespace Database\Seeders;

use App\Enums\ReferralStatus;
use App\Enums\Urgency;
use App\Models\Hospital;
use App\Models\Patient;
use App\Models\Referral;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ReferralSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $hospitals = Hospital::where('is_active', true)->get();
        $tertiary = Hospital::where('level', 'tertiary')->get();

        if ($hospitals->isEmpty()) {
            return;
        }

        $samplePatients = [
            ['reference' => 'PT-1001', 'name' => 'Anwar Hossain', 'age' => 54, 'gender' => 'male', 'blood_group' => 'A+'],
            ['reference' => 'PT-1002', 'name' => 'Salma Begum', 'age' => 31, 'gender' => 'female', 'blood_group' => 'O-'],
            ['reference' => 'PT-1003', 'name' => 'Jubair Ahmed', 'age' => 9, 'gender' => 'male', 'blood_group' => 'B+'],
            ['reference' => 'PT-1004', 'name' => 'Rina Akter', 'age' => 67, 'gender' => 'female', 'blood_group' => 'AB+'],
            ['reference' => 'PT-1005', 'name' => 'Tanvir Islam', 'age' => 28, 'gender' => 'male', 'blood_group' => 'O+'],
        ];

        foreach ($samplePatients as $patientData) {
            Patient::updateOrCreate(['reference' => $patientData['reference']], $patientData);
        }

        $statuses = [
            ReferralStatus::DRAFT,
            ReferralStatus::SENT,
            ReferralStatus::RECEIVED,
            ReferralStatus::UNDER_REVIEW,
            ReferralStatus::ACCEPTED,
            ReferralStatus::ARRIVED,
            ReferralStatus::COMPLETED,
            ReferralStatus::REJECTED,
        ];

        $urgencies = [Urgency::ROUTINE, Urgency::URGENT, Urgency::EMERGENT, Urgency::CRITICAL];
        $departments = ['Emergency', 'Cardiology', 'Neurology', 'Pediatrics', 'Orthopedics', 'General Surgery'];
        $bodyTexts = [
            'Confirmed STEMI on ECG with ongoing chest pain. Needs primary PCI capable facility.',
            'Suspected acute appendicitis, pending ultrasound. Surgical team review requested.',
            'Pediatric patient with febrile seizure, first episode. Neurology evaluation requested.',
            'Polytrauma victim from RTA, stable vitals currently, orthopedic and trauma surgery required.',
        ];
        $symptoms = [
            'Chest pain radiating to left arm, diaphoresis, shortness of breath for 3 hours.',
            'Right lower quadrant pain, guarding, rebound tenderness, low-grade fever.',
            'High-grade fever for 2 days, generalized tonic-clonic seizure lasting 3 minutes.',
            'Multiple fractures of right femur and pelvis after road traffic accident.',
        ];
        $vitalsSet = [
            ['bp' => '96/60', 'hr' => 112, 'rr' => 24, 'spo2' => 92, 'temp' => 36.8],
            ['bp' => '120/80', 'hr' => 96, 'rr' => 18, 'spo2' => 98, 'temp' => 38.1],
            ['bp' => '110/70', 'hr' => 118, 'rr' => 26, 'spo2' => 97, 'temp' => 39.2],
            ['bp' => '100/70', 'hr' => 104, 'rr' => 20, 'spo2' => 96, 'temp' => 37.0],
        ];

        $patients = Patient::all();

        foreach ($hospitals as $hospital) {
            foreach ($tertiary as $i => $targetHospital) {
                if ($targetHospital->id === $hospital->id) {
                    continue;
                }

                for ($n = 0; $n < 2; $n++) {
                    $patient = $patients->random();
                    $hcw = User::where('hospital_id', $hospital->id)->whereRelation('role', 'slug', 'healthcare_worker')->first()
                        ?? User::where('hospital_id', $hospital->id)->first();

                    if (! $hcw) {
                        continue;
                    }

                    $key = ($i + $n) % count($bodyTexts);
                    $status = $statuses[array_rand($statuses)];

                    Referral::factory()->create([
                        'referring_hospital_id' => $hospital->id,
                        'receiving_hospital_id' => $targetHospital->id,
                        'referring_user_id' => $hcw->id,
                        'patient_id' => $patient->id,
                        'status' => $status,
                        'urgency' => $urgencies[array_rand($urgencies)],
                        'is_emergency' => $status === ReferralStatus::ACCEPTED,
                        'department' => $departments[array_rand($departments)],
                        'referral_reason' => $bodyTexts[$key],
                        'symptoms' => $symptoms[$key],
                        'vitals' => $vitalsSet[$key],
                        'trauma_indicator' => $key === 3,
                        'ai_suggestion' => [
                            'urgency' => $urgencies[array_rand($urgencies)]->value,
                            'confidence' => round(fake()->randomFloat(2, 0.55, 0.95), 2),
                            'reason' => 'Fictional AI guideline for demo referrals.',
                        ],
                    ]);
                }
            }
        }
    }
}
