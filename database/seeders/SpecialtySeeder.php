<?php

namespace Database\Seeders;

use App\Models\Specialty;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SpecialtySeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $specialties = [
            'General Medicine',
            'Emergency Medicine',
            'Cardiology',
            'Orthopaedics',
            'Neurosurgery',
            'General Surgery',
            'Internal Medicine',
            'Paediatrics',
            'Obstetrics and Gynaecology',
            'Anaesthesiology',
            'Radiology',
            'Psychiatry',
            'Oncology',
            'Nephrology',
            'Pulmonology',
            'Gastroenterology',
            'Dermatology',
            'Ophthalmology',
            'ENT',
            'Urology',
        ];

        foreach ($specialties as $index => $name) {
            Specialty::updateOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name, 'sort_order' => $index + 1],
            );
        }
    }
}
