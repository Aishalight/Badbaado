<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SettingsFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_update_profile_and_upload_avatar(): void
    {
        Storage::fake('public');
        $user = User::factory()->create(['password' => 'old-password']);

        $response = $this->actingAs($user)->patch('/settings/profile', [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'title' => 'Emergency physician',
            'phone' => '+123456789',
            'avatar' => UploadedFile::fake()->create('avatar.png', 10, 'image/png'),
        ]);

        $response->assertRedirect();
        $user->refresh();

        $this->assertSame('Updated Name', $user->name);
        $this->assertSame('updated@example.com', $user->email);
        Storage::disk('public')->assertExists($user->avatar_path);
    }

    public function test_user_can_change_password_with_current_password(): void
    {
        $user = User::factory()->create(['password' => 'old-password']);

        $this->actingAs($user)->patch('/settings/password', [
            'current_password' => 'old-password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertRedirect();

        $this->assertTrue(Hash::check('new-password', $user->refresh()->password));
    }
}
