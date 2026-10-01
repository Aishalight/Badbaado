<?php

namespace Tests\Feature;

use App\Models\Hospital;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\CreatesReferralStaff;
use Tests\TestCase;

class DirectoryImageUploadTest extends TestCase
{
    use CreatesReferralStaff;
    use RefreshDatabase;

    private function systemAdmin(): User
    {
        $this->role('system_admin');

        return User::factory()->withRole('system_admin')->create();
    }

    private function hospitalAdmin(Hospital $hospital): User
    {
        return $this->staff($hospital, 'hospital_admin');
    }

    public function test_system_admin_can_create_hospital_with_logo(): void
    {
        Storage::fake('public');
        Sanctum::actingAs($this->systemAdmin());

        $this->post('/api/admin/hospitals', [
            'name' => 'Riverside General',
            'short_name' => 'Riverside',
            'code' => 'RIV',
            'logo' => UploadedFile::fake()->create('logo.png', 20, 'image/png'),
        ])->assertCreated();

        $hospital = Hospital::firstOrFail();
        Storage::disk('public')->assertExists($hospital->logo_path);
        $this->assertStringStartsWith('hospital-logos/', $hospital->logo_path);
    }

    public function test_system_admin_can_replace_hospital_logo_and_the_old_file_is_removed(): void
    {
        Storage::fake('public');
        $admin = $this->systemAdmin();
        $hospital = Hospital::factory()->create();
        $original = UploadedFile::fake()->create('first.png', 20, 'image/png');
        $original->storeAs('hospital-logos', 'first.png', 'public');
        $hospital->update(['logo_path' => 'hospital-logos/first.png']);

        Sanctum::actingAs($admin);

        $this->patch("/api/admin/hospitals/{$hospital->getKey()}", [
            'logo' => UploadedFile::fake()->create('second.png', 20, 'image/png'),
        ])->assertOk();

        $hospital->refresh();
        Storage::disk('public')->assertExists($hospital->logo_path);
        Storage::disk('public')->assertMissing('hospital-logos/first.png');
    }

    public function test_updating_a_hospital_without_a_logo_keeps_the_existing_one(): void
    {
        Storage::fake('public');
        Sanctum::actingAs($this->systemAdmin());

        $hospital = Hospital::factory()->create(['logo_path' => 'hospital-logos/keep.png']);
        UploadedFile::fake()->create('keep.png', 20, 'image/png')->storeAs('hospital-logos', 'keep.png', 'public');

        $this->patch("/api/admin/hospitals/{$hospital->getKey()}", [
            'name' => 'Renamed Hospital',
        ])->assertOk();

        $hospital->refresh();
        $this->assertSame('Renamed Hospital', $hospital->name);
        $this->assertSame('hospital-logos/keep.png', $hospital->logo_path);
        Storage::disk('public')->assertExists('hospital-logos/keep.png');
    }

    public function test_hospital_logo_must_be_an_image_within_the_size_limit(): void
    {
        Storage::fake('public');
        Sanctum::actingAs($this->systemAdmin());

        $this->post('/api/admin/hospitals', [
            'name' => 'Bad Logo Hospital',
            'short_name' => 'BadLogo',
            'code' => 'BAD',
            'logo' => UploadedFile::fake()->create('logo.pdf', 20, 'application/pdf'),
        ])->assertUnprocessable();
    }

    public function test_hospital_admin_can_create_doctor_with_photo(): void
    {
        Storage::fake('public');
        $hospital = Hospital::factory()->create();
        Sanctum::actingAs($this->hospitalAdmin($hospital));

        $this->post('/api/admin/users', [
            'name' => 'Dr Amina Yusuf',
            'email' => 'amina@example.com',
            'password' => 'password-123',
            'role_slug' => 'healthcare_worker',
            'avatar' => UploadedFile::fake()->create('amina.png', 20, 'image/png'),
        ])->assertCreated();

        $user = User::where('email', 'amina@example.com')->firstOrFail();
        Storage::disk('public')->assertExists($user->avatar_path);
        $this->assertStringStartsWith('avatars/', $user->avatar_path);
    }

    public function test_hospital_admin_can_replace_doctor_photo(): void
    {
        Storage::fake('public');
        $hospital = Hospital::factory()->create();
        $doctor = $this->staff($hospital, 'healthcare_worker');
        UploadedFile::fake()->create('old.png', 20, 'image/png')->storeAs('avatars', 'old.png', 'public');
        $doctor->update(['avatar_path' => 'avatars/old.png']);

        Sanctum::actingAs($this->hospitalAdmin($hospital));

        $this->patch("/api/admin/users/{$doctor->getKey()}", [
            'avatar' => UploadedFile::fake()->create('new.png', 20, 'image/png'),
        ])->assertOk();

        $doctor->refresh();
        Storage::disk('public')->assertExists($doctor->avatar_path);
        Storage::disk('public')->assertMissing('avatars/old.png');
    }

    public function test_updating_a_doctor_without_a_photo_keeps_the_existing_one(): void
    {
        Storage::fake('public');
        $hospital = Hospital::factory()->create();
        $doctor = $this->staff($hospital, 'healthcare_worker');
        UploadedFile::fake()->create('keep.png', 20, 'image/png')->storeAs('avatars', 'keep.png', 'public');
        $doctor->update(['avatar_path' => 'avatars/keep.png']);

        Sanctum::actingAs($this->hospitalAdmin($hospital));

        $this->patch("/api/admin/users/{$doctor->getKey()}", [
            'name' => 'Dr Renamed',
        ])->assertOk();

        $doctor->refresh();
        $this->assertSame('Dr Renamed', $doctor->name);
        $this->assertSame('avatars/keep.png', $doctor->avatar_path);
        Storage::disk('public')->assertExists('avatars/keep.png');
    }

    public function test_doctor_photo_must_be_an_image(): void
    {
        Storage::fake('public');
        $hospital = Hospital::factory()->create();
        Sanctum::actingAs($this->hospitalAdmin($hospital));

        $this->post('/api/admin/users', [
            'name' => 'Dr Bad Photo',
            'email' => 'bad@example.com',
            'password' => 'password-123',
            'role_slug' => 'healthcare_worker',
            'avatar' => UploadedFile::fake()->create('photo.pdf', 20, 'application/pdf'),
        ])->assertUnprocessable();
    }

    public function test_avatar_url_is_exposed_for_uploaded_users_and_null_otherwise(): void
    {
        $hospital = Hospital::factory()->create();
        $withPhoto = $this->staff($hospital, 'healthcare_worker');
        $withPhoto->update(['avatar_path' => 'avatars/present.png']);
        $withoutPhoto = $this->staff($hospital, 'healthcare_worker');

        $this->assertStringContainsString('avatars/present.png', $withPhoto->avatar_url);
        $this->assertNull($withoutPhoto->avatar_url);
    }

    public function test_logo_url_is_exposed_for_uploaded_hospitals_and_null_otherwise(): void
    {
        $withLogo = Hospital::factory()->create(['logo_path' => 'hospital-logos/present.png']);
        $withoutLogo = Hospital::factory()->create();

        $this->assertStringContainsString('hospital-logos/present.png', $withLogo->logo_url);
        $this->assertNull($withoutLogo->logo_url);
    }

    public function test_role_slug_is_still_restricted_when_creating_a_doctor_with_a_photo(): void
    {
        Storage::fake('public');
        $hospital = Hospital::factory()->create();
        Sanctum::actingAs($this->hospitalAdmin($hospital));

        $this->post('/api/admin/users', [
            'name' => 'Sneaky Admin',
            'email' => 'sneaky@example.com',
            'password' => 'password-123',
            'role_slug' => 'system_admin',
            'avatar' => UploadedFile::fake()->create('sneak.png', 20, 'image/png'),
        ])->assertUnprocessable();
    }

    public function test_hospital_admin_cannot_upload_a_logo_to_a_hospital(): void
    {
        Storage::fake('public');
        $hospital = Hospital::factory()->create();
        Sanctum::actingAs($this->hospitalAdmin($hospital));

        $this->patch("/api/admin/hospitals/{$hospital->getKey()}", [
            'logo' => UploadedFile::fake()->create('nope.png', 20, 'image/png'),
        ])->assertForbidden();
    }

    public function test_initials_fallback_is_derived_from_the_name(): void
    {
        $hospital = Hospital::factory()->create();
        $doctor = $this->staff($hospital, 'healthcare_worker');
        $doctor->update(['name' => 'Amina Yusuf']);

        $this->assertSame('AY', $doctor->initials);
    }
}
