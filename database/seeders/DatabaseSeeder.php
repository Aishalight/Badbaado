<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            SpecialtySeeder::class,
            // Hospitals must exist before the users, because the user seeder
            // attaches every account to a facility.
            HospitalSeeder::class,
            UserSeeder::class,
            CmsContentSeeder::class,
            SystemSettingsSeeder::class,
            ReferralSeeder::class,
            DemoFlowSeeder::class,
        ]);
    }
}
