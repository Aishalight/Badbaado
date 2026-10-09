<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\AuditActions;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_request_page_renders(): void
    {
        $this->get(route('password.request'))
            ->assertOk()
            ->assertSee('Reset your password');
    }

    public function test_reset_link_is_emailed_to_a_registered_address(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post(route('password.email'), ['email' => $user->email])
            ->assertRedirect()
            ->assertSessionHas('status', trans(Password::RESET_LINK_SENT));

        Notification::assertSentTo($user, ResetPassword::class);
    }

    /**
     * The confirmation must be identical for a known and an unknown address, so the
     * endpoint cannot be used to discover which clinicians hold an account.
     */
    public function test_unknown_address_gets_the_same_confirmation_as_a_known_one(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        // If the controller ever leaked the broker's "we can't find that user"
        // message, this second assertion would fail.
        $this->post(route('password.email'), ['email' => $user->email])
            ->assertSessionHas('status', trans(Password::RESET_LINK_SENT));

        $this->post(route('password.email'), ['email' => 'nobody@hospital.org'])
            ->assertSessionHas('status', trans(Password::RESET_LINK_SENT));

        Notification::assertSentToTimes($user, ResetPassword::class, 1);
    }

    public function test_reset_link_points_at_the_named_route(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post(route('password.email'), ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $notification) use ($user): bool {
            $url = $notification->toMail($user)->actionUrl;
            $path = (string) parse_url($url, PHP_URL_PATH);

            // The token travels as a path segment; the email is a query parameter.
            return preg_match('#^/reset-password/[A-Za-z0-9]{32,}$#', $path) === 1
                && str_contains($url, 'email='.urlencode($user->email));
        });
    }

    public function test_password_is_reset_with_a_valid_token(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $token = Password::broker()->createToken($user);

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'new-clinician-pass-1',
            'password_confirmation' => 'new-clinician-pass-1',
        ])->assertRedirect(route('login'))
            ->assertSessionHas('status', trans(Password::PASSWORD_RESET));

        $this->assertTrue(Hash::check('new-clinician-pass-1', $user->fresh()->password));
        $this->assertDatabaseMissing('password_reset_tokens', ['email' => $user->email]);
    }

    public function test_reset_is_rejected_when_confirmation_does_not_match(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $token = Password::broker()->createToken($user);

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'new-clinician-pass-1',
            'password_confirmation' => 'different-pass-1',
        ])->assertSessionHasErrors('password');

        $this->assertTrue(Hash::check('password', $user->fresh()->password));
    }

    public function test_reset_is_rejected_with_an_invalid_token(): void
    {
        $user = User::factory()->create();

        $this->post(route('password.update'), [
            'token' => 'not-a-real-token',
            'email' => $user->email,
            'password' => 'new-clinician-pass-1',
            'password_confirmation' => 'new-clinician-pass-1',
        ])->assertSessionHasErrors('email');

        $this->assertTrue(Hash::check('password', $user->fresh()->password));
    }

    public function test_completed_reset_is_audited(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $token = Password::broker()->createToken($user);

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'new-clinician-pass-1',
            'password_confirmation' => 'new-clinician-pass-1',
        ])->assertRedirect(route('login'));

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'action' => AuditActions::AUTH_PASSWORD_RESET,
        ]);
    }

    public function test_reset_request_is_rate_limited(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        foreach (range(1, 2) as $ignored) {
            $this->post(route('password.email'), ['email' => $user->email])->assertRedirect();
        }

        $this->post(route('password.email'), ['email' => $user->email])->assertStatus(429);
    }
}
