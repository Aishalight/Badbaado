<?php

namespace Tests\Feature;

use App\Models\Hospital;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesReferralStaff;
use Tests\TestCase;

class ConsoleAccessibilityTest extends TestCase
{
    use CreatesReferralStaff;
    use RefreshDatabase;

    public function test_console_layout_offers_a_skip_link_to_the_main_landmark(): void
    {
        $user = $this->staff(Hospital::factory()->create(), 'referral_coordinator');

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('href="#main-content"', escape: false);
        $response->assertSee('Skip to main content');
        $response->assertSee('id="main-content"', escape: false);
    }

    public function test_console_pages_expose_exactly_one_top_level_heading(): void
    {
        $user = $this->staff(Hospital::factory()->create(), 'referral_coordinator');

        $content = $this->actingAs($user)->get('/dashboard')->assertOk()->getContent();

        $this->assertSame(1, substr_count($content, '<h1'), 'Dashboard should render exactly one h1.');
    }

    public function test_dashboard_replaces_the_generic_heading_fallback(): void
    {
        $user = $this->staff(Hospital::factory()->create(), 'referral_coordinator');

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertDontSee('Care console');
        $response->assertSee('Coordination desk');
    }

    public function test_referral_index_sets_its_own_heading_instead_of_the_fallback(): void
    {
        $user = $this->staff(Hospital::factory()->create(), 'referral_coordinator');

        $content = $this->actingAs($user)->get('/referrals')->assertOk()->getContent();

        $this->assertStringNotContainsString('Care console', $content);
        $this->assertSame(1, substr_count($content, '<h1'), 'Referral index should render exactly one h1.');
    }

    public function test_mobile_navigation_close_button_has_an_accessible_name(): void
    {
        $user = $this->staff(Hospital::factory()->create(), 'referral_coordinator');

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertSee('data-nav-close', escape: false);
        $response->assertSee('aria-label="Close menu"', escape: false);
    }

    public function test_admin_list_pager_exposes_an_accessible_name_and_current_page(): void
    {
        $this->role('system_admin');
        $admin = User::factory()->withRole('system_admin')->create();

        Hospital::factory()->count(21)->create();

        $response = $this->actingAs($admin)->get('/admin/hospitals');

        $response->assertOk();
        $response->assertSee('aria-label="Pagination"', escape: false);
        $response->assertSee('aria-current="page"', escape: false);
        $response->assertSee('aria-label="Next page"', escape: false);
    }

    public function test_referral_filter_controls_are_labelled(): void
    {
        $user = $this->staff(Hospital::factory()->create(), 'referral_coordinator');

        $response = $this->actingAs($user)->get('/referrals');

        $response->assertOk();
        $response->assertSee('for="filter-status"', escape: false);
        $response->assertSee('for="filter-urgency"', escape: false);
    }

    public function test_system_settings_inputs_are_associated_with_their_labels(): void
    {
        $this->role('system_admin');
        $admin = User::factory()->withRole('system_admin')->create();

        $response = $this->actingAs($admin)->get('/admin/config');

        $response->assertOk();
        $response->assertSee('id="setting-auth-registration-enabled"', escape: false);
        $response->assertSee('for="setting-auth-registration-enabled"', escape: false);
        $response->assertSee('name="settings[auth.registration_enabled]"', escape: false);
    }
}
