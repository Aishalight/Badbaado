<?php

namespace Tests\Feature;

use App\Enums\ProviderApplicationStatus;
use App\Enums\ProviderApplicationType;
use App\Enums\UserStatus;
use App\Models\Hospital;
use App\Models\ProviderApplication;
use App\Models\Specialty;
use App\Models\User;
use App\Services\SettingsService;
use Database\Seeders\SpecialtySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesReferralStaff;
use Tests\TestCase;

class ProviderRegistrationTest extends TestCase
{
    use CreatesReferralStaff;
    use RefreshDatabase;

    /**
     * @return array<string, mixed>
     */
    private function doctorPayload(array $overrides = []): array
    {
        return array_merge([
            'type' => ProviderApplicationType::DOCTOR->value,
            'name' => 'Dr. Ayesha Siddiqua',
            'email' => 'ayesha.siddiqua@example.com',
            'phone' => '+8801700000001',
            'title' => 'Consultant Cardiologist',
            'specialty_id' => $this->cardiologyId(),
            'license_number' => 'A-4471',
            'password' => 'password',
            'password_confirmation' => 'password',
        ], $overrides);
    }

    /**
     * @return array<string, mixed>
     */
    private function hospitalPayload(array $overrides = []): array
    {
        return array_merge([
            'type' => ProviderApplicationType::HOSPITAL->value,
            'name' => 'Nusrat Jahan',
            'email' => 'admin@citymedical.example',
            'facility_name' => 'City Medical Centre',
            'location' => 'Dhaka, Bangladesh',
            'level' => 'tertiary',
            'password' => 'password',
            'password_confirmation' => 'password',
        ], $overrides);
    }

    private function cardiologyId(): int
    {
        $this->seed(SpecialtySeeder::class);

        return Specialty::where('slug', 'cardiology')->firstOrFail()->getKey();
    }

    public function test_registration_page_renders_with_both_provider_types(): void
    {
        $this->seed(SpecialtySeeder::class);

        $this->get(route('register'))
            ->assertOk()
            ->assertSeeText('Independent doctor')
            ->assertSeeText('Hospital')
            ->assertSeeText('Cardiology');
    }

    public function test_hospital_signup_creates_a_pending_account_and_application(): void
    {
        $response = $this->post(route('register.store'), $this->hospitalPayload());

        $response->assertRedirect(route('login'));

        $user = User::where('email', 'admin@citymedical.example')->firstOrFail();
        $this->assertSame(UserStatus::PENDING, $user->status);
        $this->assertNull($user->role_id, 'A pending applicant must not hold a role.');
        $this->assertNull($user->hospital_id, 'A pending applicant must not belong to a facility yet.');

        $application = ProviderApplication::firstOrFail();
        $this->assertSame(ProviderApplicationType::HOSPITAL, $application->type);
        $this->assertSame(ProviderApplicationStatus::PENDING, $application->status);
        $this->assertSame($user->getKey(), $application->user_id);
    }

    public function test_hospital_application_payload_does_not_store_the_password(): void
    {
        $this->post(route('register.store'), $this->hospitalPayload());

        $payload = ProviderApplication::firstOrFail()->payload;

        $this->assertArrayNotHasKey('password', $payload);
        $this->assertArrayNotHasKey('password_confirmation', $payload);
        $this->assertSame('City Medical Centre', $payload['facility_name']);
    }

    public function test_doctor_signup_records_the_chosen_specialty_on_the_account(): void
    {
        $this->post(route('register.store'), $this->doctorPayload());

        $user = User::where('email', 'ayesha.siddiqua@example.com')->firstOrFail();

        $this->assertSame(UserStatus::PENDING, $user->status);
        $this->assertSame($this->cardiologyId(), $user->specialty_id);
    }

    public function test_api_registration_uses_the_pending_verification_workflow(): void
    {
        app(SettingsService::class)->set('auth.registration_enabled', true, 'boolean');

        $response = $this->postJson('/api/register', $this->doctorPayload());

        $response->assertStatus(202)
            ->assertJsonPath('status', ProviderApplicationStatus::PENDING->value)
            ->assertJsonMissing(['token']);

        $user = User::where('email', 'ayesha.siddiqua@example.com')->firstOrFail();

        $this->assertSame(UserStatus::PENDING, $user->status);
        $this->assertNull($user->role_id);
        $this->assertDatabaseHas('provider_applications', [
            'user_id' => $user->getKey(),
            'status' => ProviderApplicationStatus::PENDING->value,
        ]);
    }

    public function test_doctor_signup_returns_422_without_a_specialty(): void
    {
        $this->post(route('register.store'), $this->doctorPayload(['specialty_id' => null]))
            ->assertSessionHasErrors('specialty_id');

        $this->assertDatabaseCount('users', 0);
    }

    public function test_doctor_signup_returns_422_without_a_registration_number(): void
    {
        $this->post(route('register.store'), $this->doctorPayload(['license_number' => null]))
            ->assertSessionHasErrors('license_number');

        $this->assertDatabaseCount('users', 0);
    }

    public function test_hospital_signup_returns_422_without_a_facility_name(): void
    {
        $this->post(route('register.store'), $this->hospitalPayload(['facility_name' => null]))
            ->assertSessionHasErrors('facility_name');

        $this->assertDatabaseCount('users', 0);
    }

    public function test_doctor_signup_rejects_a_facility_name(): void
    {
        $this->post(route('register.store'), $this->doctorPayload(['facility_name' => 'Somewhere General']))
            ->assertSessionHasErrors('facility_name');

        $this->assertDatabaseCount('users', 0);
    }

    public function test_hospital_signup_rejects_a_registration_number(): void
    {
        $this->post(route('register.store'), $this->hospitalPayload(['license_number' => 'A-1']))
            ->assertSessionHasErrors('license_number');

        $this->assertDatabaseCount('users', 0);
    }

    public function test_signup_returns_422_when_the_email_is_already_registered(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);

        $this->post(route('register.store'), $this->doctorPayload(['email' => 'taken@example.com']))
            ->assertSessionHasErrors('email');

        $this->assertDatabaseCount('provider_applications', 0);
    }

    public function test_signup_returns_422_for_an_unknown_provider_type(): void
    {
        $this->post(route('register.store'), $this->doctorPayload(['type' => 'pharmacy']))
            ->assertSessionHasErrors('type');
    }

    public function test_a_pending_applicant_cannot_reach_the_dashboard(): void
    {
        $user = User::factory()->pending()->withRole('healthcare_worker')->create();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertRedirect(route('pending'));
    }

    public function test_a_pending_applicant_is_redirected_from_the_admin_user_list(): void
    {
        $this->role('system_admin');
        $user = User::factory()->pending()->withRole('system_admin')->create();

        $this->actingAs($user)
            ->get(route('admin.users'))
            ->assertRedirect(route('pending'));
    }

    public function test_a_pending_applicant_can_open_the_status_page(): void
    {
        $this->post(route('register.store'), $this->hospitalPayload());
        $user = User::where('email', 'admin@citymedical.example')->firstOrFail();

        $this->actingAs($user)
            ->get(route('pending'))
            ->assertOk()
            ->assertSeeText('Pending review')
            ->assertSeeText('City Medical Centre');
    }

    public function test_the_status_page_redirects_guests_to_login(): void
    {
        $this->get(route('pending'))->assertRedirect(route('login'));
    }

    public function test_a_pending_applicant_signing_in_lands_on_the_status_page(): void
    {
        $user = User::factory()->pending()->create();

        $this->post(route('login.attempt'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('pending'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_a_suspended_doctor_is_never_offered_as_a_referral_target(): void
    {
        $this->role('healthcare_worker');
        $practice = Hospital::factory()->practice()->create();
        $suspended = User::factory()->atHospital($practice->getKey())
            ->withRole('healthcare_worker')
            ->suspended()
            ->create();

        $offered = $practice->referralStaff()->pluck('users.id');

        $this->assertFalse($offered->contains($suspended->getKey()));
    }
}
