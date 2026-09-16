<?php

namespace Database\Seeders;

use App\Models\Hospital;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class HospitalSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $hospitals = [
            [
                'name' => 'Al-Hilal Teaching Hospital',
                'short_name' => 'Al-Hilal',
                'code' => 'AHL',
                'location' => 'Shegunbagicha, Dhaka',
                'phone' => '+880 2 5558 4101',
                'email' => 'info@alhilal.teaching.bd',
                'level' => 'tertiary',
                'is_active' => true,
            ],
            [
                'name' => 'Shifa Community Medical College',
                'short_name' => 'Shifa Medical',
                'code' => 'SHM',
                'location' => 'Mohakhali, Dhaka',
                'phone' => '+880 2 5559 7200',
                'email' => 'info@shifamedical.bd',
                'level' => 'tertiary',
                'is_active' => true,
            ],
            [
                'name' => 'Nadia Specialist Hospital',
                'short_name' => 'Nadia',
                'code' => 'NSH',
                'location' => 'Dhanmondi, Dhaka',
                'phone' => '+880 2 5560 3814',
                'email' => 'care@nadia-specialist.bd',
                'level' => 'secondary',
                'is_active' => true,
            ],
            [
                'name' => 'Greenfields General Hospital',
                'short_name' => 'Greenfields',
                'code' => 'GFG',
                'location' => 'Gulshan-2, Dhaka',
                'phone' => '+880 2 5561 2209',
                'email' => 'hello@greenfields.bd',
                'level' => 'secondary',
                'is_active' => true,
            ],
            [
                'name' => 'Rudra District Hospital',
                'short_name' => 'Rudra',
                'code' => 'RUD',
                'location' => 'Rudra Sadar, Khulna',
                'phone' => '+880 41 7789 003',
                'email' => 'admin@rudra.district.bd',
                'level' => 'secondary',
                'is_active' => true,
            ],
            [
                'name' => 'South Delta City Hospital',
                'short_name' => 'South Delta',
                'code' => 'SDC',
                'location' => 'Agrabad, Chattogram',
                'phone' => '+880 31 6601 412',
                'email' => 'info@southdelta.bd',
                'level' => 'tertiary',
                'is_active' => true,
            ],
            [
                'name' => 'Mahabub Medical & Diagnostic',
                'short_name' => 'Mahabub',
                'code' => 'MHB',
                'location' => 'Rajshahi Sadar, Rajshahi',
                'phone' => '+880 721 66290 5',
                'email' => 'info@mahabub-medical.bd',
                'level' => 'secondary',
                'is_active' => true,
            ],
            [
                'name' => 'Gazipur Adhunik General',
                'short_name' => 'Gazipur Adhunik',
                'code' => 'GZG',
                'location' => 'Chowrasta, Gazipur',
                'phone' => '+880 2 5563 099',
                'email' => 'info@gazipur-adhunik.bd',
                'level' => 'primary',
                'is_active' => true,
            ],
        ];

        foreach ($hospitals as $hospital) {
            Hospital::updateOrCreate(['code' => $hospital['code']], $hospital);
        }
    }
}
