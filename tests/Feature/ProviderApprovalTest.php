<?php

namespace Tests\Feature;

use App\Enums\ProviderApplicationStatus;
use App\Enums\ProviderApplicationType;
use App\Enums\UserStatus;
use App\Models\Hospital;
use App\Models\ProviderApplication;
use App\Models\Role;
use App\Models\Specialty;
use App\Models\User;
use App\Support\AuditActions;
use Database\Seeders\SpecialtySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProviderApprovalTest extends TestCase
{
    use RefreshDatabase;

    private function reviewer(): User
    {
        Role::firstOrCreate(['slug' => 'system_admin'], ['name' => 'System Admin', 'description' => '']);

        return User::factory()->withRole('system_admin')->create();
    }

    private function hospitalApplication(?string $key = null): ProviderApplication
    {
        $key ??= (string) random_int(1, 999999);

        return ProviderApplication::create([
            'type' => ProviderApplicationType::HOSPITAL,
            'status' => ProviderApplicationStatus::PENDING,
            'user_id' => User::factory()->pending()->create(['email' => "admin+{$key}@citymedical.example"])->getKey(),
            'payload' => [
                'name' => 'Nusrat Jahan',
                'email' => "admin+{$key}@citymedical.example",
                'facility_name' => 'City Medical Centre',
                'location' => 'Dhaka, Bangladesh',
                'level' => 'tertiary',
            ],
        ]);
    }

    private function doctorApplication(?string $key = null): ProviderApplication
    {
        $this->seed(SpecialtySeeder::class);
        $key ??= (string) random_int(1, 999999);
        $specialty = Specialty::where('slug', 'cardiology')->firstOrFail();

        $user = User::factory()->pending()->create([
            'name' => 'Dr. Ayesha Siddiqua',
            'email' => "ayesha+{$key}@example.com",
            'specialty_id' => $specialty->getKey(),
        ]);

        return ProviderApplication::create([
            'type' => ProviderApplicationType::DOCTOR,
            'status' => ProviderApplicationStatus::PENDING,
            'user_id' => $user->getKey(),
            'payload' => [
                'name' => 'Dr. Ayesha Siddiqua',
                'email' => "ayesha+{$key}@example.com",
                'title' => 'Consultant Cardiologist',
                'specialty_id' => $specialty->getKey(),
                'license_number' => 'A-4471',
            ],
        ]);
    }

    public function test_approving_a_hospital_application_creates_a_hospital_and_activates_its_admin(): void
    {
        $reviewer = $this->reviewer();
        $application = $this->hospitalApplication();

        $this->actingAs($reviewer)
            ->post(route('admin.applications.approve', $application))
            ->assertRedirect(route('admin.applications'));

        $user = $application->user->refresh();
        $this->assertSame(UserStatus::ACTIVE, $user->status);
        $this->assertSame('hospital_admin', $user->role->slug);
        $this->assertNotNull($user->hospital_id);
    }

    public function test_approving_a_hospital_application_creates_a_hospital_kind_facility(): void
    {
        $reviewer = $this->reviewer();
        $application = $this->hospitalApplication();

        $this->actingAs($reviewer)->post(route('admin.applications.approve', $application));

        $hospital = $application->refresh()->hospital;

        $this->assertSame('hospital', $hospital->kind);
        $this->assertSame('City Medical Centre', $hospital->name);
        $this->assertSame('tertiary', $hospital->level);
    }

    public function test_approving_a_hospital_application_records_the_reviewer_and_timestamp(): void
    {
        $reviewer = $this->reviewer();
        $application = $this->hospitalApplication();

        $this->actingAs($reviewer)->post(route('admin.applications.approve', $application));

        $application->refresh();

        $this->assertSame(ProviderApplicationStatus::APPROVED, $application->status);
        $this->assertSame($reviewer->getKey(), $application->reviewed_by);
        $this->assertNotNull($application->reviewed_at);
    }

    public function test_approving_a_doctor_application_creates_a_private_practice(): void
    {
        $reviewer = $this->reviewer();
        $application = $this->doctorApplication();

        $this->actingAs($reviewer)->post(route('admin.applications.approve', $application));

        $hospital = $application->refresh()->hospital;

        $this->assertSame('practice', $hospital->kind);
        $this->assertStringContainsString('Dr. Ayesha Siddiqua', $hospital->name);
        $this->assertStringContainsString('Private Practice', $hospital->name);
    }

    public function test_approving_a_doctor_application_activates_them_as_a_healthcare_worker(): void
    {
        $reviewer = $this->reviewer();
        $application = $this->doctorApplication();

        $this->actingAs($reviewer)->post(route('admin.applications.approve', $application));

        $user = $application->user->refresh();

        $this->assertSame(UserStatus::ACTIVE, $user->status);
        $this->assertSame('healthcare_worker', $user->role->slug);
        $this->assertSame('practice', $user->hospital->kind);
    }

    public function test_two_approved_doctors_never_share_a_facility_code(): void
    {
        $reviewer = $this->reviewer();
        $first = $this->doctorApplication();
        $second = $this->doctorApplication();

        $this->actingAs($reviewer)->post(route('admin.applications.approve', $first));
        $this->actingAs($reviewer)->post(route('admin.applications.approve', $second));

        $codes = Hospital::query()->pluck('code');

        $this->assertSame($codes->count(), $codes->unique()->count());
        $this->assertSame($codes->count(), Hospital::query()->pluck('short_name')->unique()->count());
    }

    public function test_an_approved_doctor_can_reach_the_dashboard(): void
    {
        $reviewer = $this->reviewer();
        $application = $this->doctorApplication();

        $this->actingAs($reviewer)->post(route('admin.applications.approve', $application));

        $this->actingAs($application->user->refresh())
            ->get(route('dashboard'))
            ->assertOk();
    }

    public function test_rejecting_an_application_closes_the_account_and_stores_the_reason(): void
    {
        $reviewer = $this->reviewer();
        $application = $this->hospitalApplication();

        $this->actingAs($reviewer)
            ->post(route('admin.applications.reject', $application), [
                'rejection_reason' => 'Registration number could not be verified.',
            ])
            ->assertRedirect(route('admin.applications'));

        $application->refresh();

        $this->assertSame(ProviderApplicationStatus::REJECTED, $application->status);
        $this->assertSame('Registration number could not be verified.', $application->rejection_reason);
        $this->assertSame(UserStatus::REJECTED, $application->user->refresh()->status);
    }

    public function test_rejecting_an_application_requires_a_reason(): void
    {
        $reviewer = $this->reviewer();
        $application = $this->hospitalApplication();

        $this->actingAs($reviewer)
            ->post(route('admin.applications.reject', $application), ['rejection_reason' => ''])
            ->assertSessionHasErrors('rejection_reason');

        $this->assertSame(ProviderApplicationStatus::PENDING, $application->refresh()->status);
    }

    public function test_a_rejected_applicant_cannot_reach_the_dashboard(): void
    {
        $reviewer = $this->reviewer();
        $application = $this->hospitalApplication();

        $this->actingAs($reviewer)->post(route('admin.applications.reject', $application), [
            'rejection_reason' => 'Not eligible.',
        ]);

        $this->actingAs($application->user->refresh())
            ->get(route('dashboard'))
            ->assertRedirect(route('pending'));
    }

    public function test_a_hospital_admin_cannot_approve_applications(): void
    {
        Role::firstOrCreate(['slug' => 'hospital_admin'], ['name' => 'Hospital Admin', 'description' => '']);
        $admin = User::factory()->withRole('hospital_admin')->create();
        $application = $this->hospitalApplication();

        $this->actingAs($admin)
            ->post(route('admin.applications.approve', $application))
            ->assertForbidden();

        $this->assertSame(ProviderApplicationStatus::PENDING, $application->refresh()->status);
    }

    public function test_a_guest_cannot_approve_applications(): void
    {
        $application = $this->hospitalApplication();

        $this->post(route('admin.applications.approve', $application))
            ->assertRedirect(route('login'));

        $this->assertSame(ProviderApplicationStatus::PENDING, $application->refresh()->status);
    }

    public function test_approving_an_already_approved_application_changes_nothing(): void
    {
        $reviewer = $this->reviewer();
        $application = $this->hospitalApplication();

        $this->actingAs($reviewer)->post(route('admin.applications.approve', $application));
        $firstHospitalId = $application->refresh()->hospital_id;

        $this->actingAs($reviewer)->post(route('admin.applications.approve', $application));

        $this->assertSame($firstHospitalId, $application->refresh()->hospital_id);
        $this->assertSame(1, Hospital::query()->count());
    }

    public function test_the_review_queue_lists_pending_applications_first(): void
    {
        $reviewer = $this->reviewer();
        $this->hospitalApplication();

        $this->actingAs($reviewer)
            ->get(route('admin.applications'))
            ->assertOk()
            ->assertSeeText('City Medical Centre')
            ->assertSeeText('Pending review');
    }

    public function test_the_review_queue_filters_by_status(): void
    {
        $reviewer = $this->reviewer();
        $approved = $this->hospitalApplication();
        $this->actingAs($reviewer)->post(route('admin.applications.approve', $approved));
        $this->hospitalApplication();

        $this->actingAs($reviewer)
            ->get(route('admin.applications', ['status' => 'approved']))
            ->assertOk()
            ->assertSeeText('Approved');

        $this->assertSame(1, ProviderApplication::pending()->count());
    }

    public function test_approval_is_audited(): void
    {
        $reviewer = $this->reviewer();
        $application = $this->hospitalApplication();

        $this->actingAs($reviewer)->post(route('admin.applications.approve', $application));

        $this->assertDatabaseHas('audit_logs', [
            'action' => AuditActions::PROVIDER_APPLICATION_APPROVED,
            'entity_type' => 'ProviderApplication',
            'entity_id' => $application->getKey(),
        ]);
    }
}
