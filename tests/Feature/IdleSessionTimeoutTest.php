<?php

namespace Tests\Feature;

use App\Models\Hospital;
use App\Models\User;
use App\Services\SettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesReferralStaff;
use Tests\TestCase;

class IdleSessionTimeoutTest extends TestCase
{
    use CreatesReferralStaff;
    use RefreshDatabase;

    private function setTimeout(int $minutes): void
    {
        app(SettingsService::class)->set('auth.session_timeout_minutes', $minutes, 'integer');
    }

    public function test_active_session_survives_requests_within_the_timeout(): void
    {
        $user = $this->staff(Hospital::factory()->create(), 'referral_coordinator');
        $this->setTimeout(30);

        $this->actingAs($user)->get('/dashboard')->assertOk();
        $this->actingAs($user)->get('/referrals')->assertOk();
    }

    public function test_idle_session_is_signed_out_after_the_timeout(): void
    {
        $user = $this->staff(Hospital::factory()->create(), 'referral_coordinator');
        $this->setTimeout(30);

        $this->actingAs($user)
            ->withSession(['last_activity_at' => time() - (31 * 60)])
            ->get('/dashboard')
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_timeout_is_disabled_when_configured_to_zero(): void
    {
        $user = $this->staff(Hospital::factory()->create(), 'referral_coordinator');
        $this->setTimeout(0);

        $this->actingAs($user)
            ->withSession(['last_activity_at' => time() - (60 * 60 * 24)])
            ->get('/dashboard')
            ->assertOk();
    }

    public function test_guests_are_never_timed_out_by_the_middleware(): void
    {
        $this->setTimeout(1);

        $this->get('/login')->assertOk();
    }

    public function test_timeout_does_not_block_the_health_check(): void
    {
        $this->setTimeout(1);

        $this->get('/up')->assertOk();
    }

    public function test_last_activity_is_refreshed_on_each_request(): void
    {
        $user = User::factory()->withRole('healthcare_worker')->create();
        $this->setTimeout(60);

        $this->actingAs($user)
            ->withSession(['last_activity_at' => time() - 600])
            ->get('/dashboard')
            ->assertOk();

        $this->assertGreaterThan(time() - 5, (int) session('last_activity_at'));
    }
}
