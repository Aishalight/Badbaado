<?php

namespace Database\Seeders;

use App\Models\Hospital;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $systemAdmin = Role::where('slug', 'system_admin')->firstOrFail();
        $hospitalAdmin = Role::where('slug', 'hospital_admin')->firstOrFail();
        $coordinator = Role::where('slug', 'referral_coordinator')->firstOrFail();
        $hcw = Role::where('slug', 'healthcare_worker')->firstOrFail();

        User::updateOrCreate(
            ['email' => 'admin@badbaado.bd'],
            [
                'name' => 'BADBAADO System Admin',
                'password' => Hash::make('password'),
                'role_id' => $systemAdmin->id,
                'is_active' => true,
            ]
        );

        $demoref = Hospital::where('code', 'AHL')->first();

        User::updateOrCreate(
            ['email' => 'dr.rahman@alhilal.bd'],
            [
                'name' => 'Dr. Farhana Rahman',
                'title' => 'Senior Registrar — Emergency',
                'password' => Hash::make('password'),
                'hospital_id' => $demoref->id,
                'role_id' => $hcw->id,
                'is_active' => true,
            ]
        );

        User::updateOrCreate(
            ['email' => 'coordinator@alhilal.bd'],
            [
                'name' => 'Mehrab Hasan',
                'title' => 'Referral Coordinator',
                'password' => Hash::make('password'),
                'hospital_id' => $demoref->id,
                'role_id' => $coordinator->id,
                'is_active' => true,
            ]
        );

        User::updateOrCreate(
            ['email' => 'hospital.admin@alhilal.bd'],
            [
                'name' => 'Nusrat Jahan',
                'title' => 'Hospital Administrator',
                'password' => Hash::make('password'),
                'hospital_id' => $demoref->id,
                'role_id' => $hospitalAdmin->id,
                'is_active' => true,
            ]
        );

        foreach (Hospital::where('is_active', true)->get() as $hospital) {
            if ($hospital->id === $demoref->id) {
                continue;
            }

            $emailPrefix = strtolower($hospital->code);

            User::updateOrCreate(
                ['email' => "admin@{$emailPrefix}.badbaado.bd"],
                [
                    'name' => "{$hospital->short_name} Hospital Admin",
                    'title' => 'Hospital Administrator',
                    'password' => Hash::make('password'),
                    'hospital_id' => $hospital->id,
                    'role_id' => $hospitalAdmin->id,
                    'is_active' => true,
                ]
            );

            User::updateOrCreate(
                ['email' => "worker@{$emailPrefix}.badbaado.bd"],
                [
                    'name' => "{$hospital->short_name} Healthcare Worker",
                    'title' => 'Healthcare Worker',
                    'password' => Hash::make('password'),
                    'hospital_id' => $hospital->id,
                    'role_id' => $hcw->id,
                    'is_active' => true,
                ]
            );
        }
    }
}
