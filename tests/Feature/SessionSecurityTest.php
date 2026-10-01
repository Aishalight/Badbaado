<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\AuditActions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SessionSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'session.driver' => 'database',
            'session.table' => 'sessions',
            'session.connection' => null,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function sessionRow(string $id, int $userId): array
    {
        return [
            'id' => $id,
            'user_id' => $userId,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
            'payload' => base64_encode(serialize([])),
            'last_activity' => time(),
        ];
    }

    public function test_changing_password_removes_other_sessions(): void
    {
        $user = User::factory()->create(['password' => 'old-password']);

        DB::table('sessions')->insert([
            $this->sessionRow('other-device-1', $user->getKey()),
            $this->sessionRow('other-device-2', $user->getKey()),
        ]);

        $this->actingAs($user)->patch('/settings/password', [
            'current_password' => 'old-password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertRedirect();

        $this->assertSame(0, DB::table('sessions')->whereIn('id', ['other-device-1', 'other-device-2'])->count());
    }

    public function test_changing_password_preserves_only_the_current_session_row(): void
    {
        $user = User::factory()->create(['password' => 'old-password']);

        DB::table('sessions')->insert($this->sessionRow('other-device-1', $user->getKey()));

        $this->actingAs($user)->patch('/settings/password', [
            'current_password' => 'old-password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertRedirect();

        $surviving = DB::table('sessions')->where('user_id', $user->getKey())->pluck('id');

        $this->assertCount(1, $surviving, 'Only the session that made the change should survive.');
        $this->assertNotContains('other-device-1', $surviving);
    }

    public function test_changing_password_keeps_the_requesting_user_signed_in(): void
    {
        $user = User::factory()->create(['password' => 'old-password']);

        $this->actingAs($user)->patch('/settings/password', [
            'current_password' => 'old-password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertRedirect();

        $this->assertAuthenticatedAs($user);
    }

    public function test_changing_password_clears_the_remember_token(): void
    {
        $user = User::factory()->create([
            'password' => 'old-password',
            'remember_token' => 'a-remember-me-token',
        ]);

        $this->actingAs($user)->patch('/settings/password', [
            'current_password' => 'old-password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertRedirect();

        $this->assertNull($user->refresh()->remember_token);
    }

    public function test_sessions_table_exists_for_the_database_session_driver(): void
    {
        $this->assertTrue(
            Schema::hasTable('sessions'),
            'The database session driver requires a migrated sessions table.'
        );
    }

    public function test_audit_log_records_the_password_change(): void
    {
        $user = User::factory()->create(['password' => 'old-password']);

        $this->actingAs($user)->patch('/settings/password', [
            'current_password' => 'old-password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertRedirect();

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->getKey(),
            'action' => AuditActions::AUTH_PASSWORD_CHANGED,
        ]);
    }
}
