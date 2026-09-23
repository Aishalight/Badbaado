<?php

namespace Tests\Feature;

use App\Models\Hospital;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesReferralStaff;
use Tests\TestCase;

class ReferralCreatePageTest extends TestCase
{
    use CreatesReferralStaff;
    use RefreshDatabase;

    public function test_create_page_is_visible_to_referral_staff(): void
    {
        $referring = Hospital::factory()->create();
        $receiving = Hospital::factory()->create(['is_active' => true]);
        $coordinator = $this->staff($referring, 'referral_coordinator');

        $response = $this->actingAs($coordinator)->get('/referrals/new');

        $response->assertOk();
        $response->assertSee('New referral');
    }

    public function test_create_page_is_forbidden_for_hospital_admin(): void
    {
        $hospital = Hospital::factory()->create();
        $admin = $this->staff($hospital, 'hospital_admin');

        $this->actingAs($admin)->get('/referrals/new')->assertForbidden();
    }

    public function test_create_page_is_forbidden_for_system_admin(): void
    {
        $this->role('system_admin');
        $admin = User::factory()->withRole('system_admin')->create();

        $this->actingAs($admin)->get('/referrals/new')->assertForbidden();
    }
}
