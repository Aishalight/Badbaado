<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            ['slug' => 'healthcare_worker', 'name' => 'Health Care Worker', 'description' => 'Creates and sends referrals (e.g. doctor, nurse)'],
            ['slug' => 'referral_coordinator', 'name' => 'Referral Coordinator', 'description' => 'Manages one or more hospitals\' incoming and outgoing referrals'],
            ['slug' => 'hospital_admin', 'name' => 'Hospital Admin', 'description' => 'Administer hospital data, users, and referral configuration'],
            ['slug' => 'system_admin', 'name' => 'System Admin', 'description' => 'Global platform administration and support'],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(['slug' => $role['slug']], $role);
        }
    }
}
