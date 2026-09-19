<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\AuditLog;
use App\Models\Backup;
use App\Models\CmsContent;
use App\Models\Faq;
use App\Models\Hospital;
use App\Models\Notification;
use App\Models\Referral;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\SettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\CreatesReferralStaff;
use Tests\TestCase;

class AdminCommandCenterFeatureTest extends TestCase
{
    use CreatesReferralStaff;
    use RefreshDatabase;

    private function systemAdmin(): User
    {
        $this->role('system_admin');

        return User::factory()->withRole('system_admin')->create();
    }

    public function test_system_admin_can_open_command_center(): void
    {
        $this->actingAs($this->systemAdmin())
            ->get('/admin')
            ->assertOk()
            ->assertSee('Command Center');
    }

    public function test_non_system_admin_cannot_open_command_center(): void
    {
        $this->actingAs($this->staff(Hospital::factory()->create(), 'hospital_admin'))
            ->get('/admin')
            ->assertForbidden();
    }

    public function test_system_admin_can_open_every_command_center_page(): void
    {
        $admin = $this->systemAdmin();

        $pages = [
            '/admin/referrals',
            '/admin/security',
            '/admin/audit',
            '/admin/health',
            '/admin/cms',
            '/admin/config',
            '/admin/reports',
            '/admin/backups',
            '/admin/alerts',
            '/admin/hospitals',
            '/admin/users',
        ];

        foreach ($pages as $page) {
            $this->actingAs($admin)->get($page)->assertOk();
        }
    }

    public function test_command_center_shows_real_referral_data(): void
    {
        $this->role('system_admin');
        $a = Hospital::factory()->create();
        $b = Hospital::factory()->create();

        Referral::factory()->create([
            'referring_hospital_id' => $a->getKey(),
            'receiving_hospital_id' => $b->getKey(),
            'urgency' => 'urgent',
        ]);

        $this->actingAs(User::factory()->withRole('system_admin')->create())
            ->get('/admin/referrals')
            ->assertOk()
            ->assertSee($a->name)
            ->assertSee($b->name);
    }

    public function test_audit_page_reflects_actual_audit_log_entries(): void
    {
        $admin = $this->systemAdmin();

        AuditLog::create([
            'user_id' => $admin->getKey(),
            'action' => 'user_listed',
            'entity_type' => User::class,
            'entity_id' => $admin->getKey(),
            'ip_address' => '127.0.0.1',
            'user_agent' => 'phpunit',
        ]);

        $this->actingAs($admin)
            ->get('/admin/audit')
            ->assertOk()
            ->assertSee('user_listed');
    }

    public function test_cms_update_persists_and_is_audited(): void
    {
        $admin = $this->systemAdmin();
        Sanctum::actingAs($admin);

        $content = CmsContent::create([
            'key' => 'hero.title',
            'section' => 'hero',
            'title' => 'Hero headline',
            'content' => 'Connecting|Hospitals,|Connecting Care',
            'is_active' => true,
        ]);

        $this->patchJson("/api/admin/cms/{$content->getKey()}", [
            'content' => 'New|Hero|Title',
        ])->assertOk();

        $this->assertDatabaseHas('cms_contents', [
            'id' => $content->getKey(),
            'content' => 'New|Hero|Title',
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'cms_updated',
            'user_id' => $admin->getKey(),
        ]);
    }

    public function test_announcement_lifecycle_via_api(): void
    {
        $admin = $this->systemAdmin();
        Sanctum::actingAs($admin);

        $this->postJson('/api/admin/announcements', [
            'title' => 'Maintenance window',
            'body' => 'Scheduled downtime Sunday 02:00–03:00.',
        ])->assertCreated();

        $announcement = Announcement::firstOrFail();
        $this->assertEquals($admin->getKey(), $announcement->author_id);
        $this->assertNotNull($announcement->published_at);

        $this->patchJson("/api/admin/announcements/{$announcement->getKey()}", [
            'body' => 'Rescheduled to 04:00.',
        ])->assertOk();
        $this->assertSame('Rescheduled to 04:00.', $announcement->refresh()->body);

        $this->deleteJson("/api/admin/announcements/{$announcement->getKey()}")->assertOk();
        $this->assertDatabaseMissing('announcements', ['id' => $announcement->getKey()]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'announcement_deleted']);
    }

    public function test_faq_lifecycle_via_api(): void
    {
        $admin = $this->systemAdmin();
        Sanctum::actingAs($admin);

        $this->postJson('/api/admin/faqs', [
            'question' => 'How are referrals sent?',
            'answer' => 'Through the console referral form.',
        ])->assertCreated();

        $faq = Faq::firstOrFail();

        $this->patchJson("/api/admin/faqs/{$faq->getKey()}", [
            'answer' => 'Through the referral form in the console.',
        ])->assertOk();
        $this->assertSame('Through the referral form in the console.', $faq->refresh()->answer);

        $this->deleteJson("/api/admin/faqs/{$faq->getKey()}")->assertOk();
        $this->assertDatabaseMissing('faqs', ['id' => $faq->getKey()]);
    }

    public function test_settings_update_persists_typed_values_and_audits(): void
    {
        $admin = $this->systemAdmin();
        Sanctum::actingAs($admin);
        SystemSetting::create([
            'key' => 'backups.retention_days',
            'value' => '30',
            'type' => 'integer',
            'group' => 'Operations',
            'label' => 'Backup retention',
            'description' => '',
            'is_protected' => false,
        ]);

        $this->patchJson('/api/admin/settings', [
            'settings' => ['backups.retention_days' => '14'],
        ])->assertOk();

        $this->assertSame('14', SystemSetting::where('key', 'backups.retention_days')->value('value'));
        $this->assertDatabaseHas('audit_logs', ['action' => 'settings_updated']);
    }

    public function test_settings_service_rebuilds_corrupted_cache(): void
    {
        $admin = $this->systemAdmin();
        $god = app(SettingsService::class);

        SystemSetting::create([
            'key' => 'backups.retention_days',
            'value' => '30',
            'type' => 'integer',
            'group' => 'Operations',
            'label' => 'Backup retention',
            'description' => '',
            'is_protected' => false,
        ]);

        cache()->put('badbaado.system_settings', new \stdClass);

        $this->assertSame(30, $god->get('backups.retention_days', 30));
        $this->assertTrue($god->all() instanceof Collection);
    }

    public function test_backup_create_and_delete_via_api(): void
    {
        $admin = $this->systemAdmin();
        Sanctum::actingAs($admin);

        $response = $this->postJson('/api/admin/backups', [
            'notes' => 'Pre-upgrade snapshot',
        ])->assertCreated();

        $backup = Backup::firstOrFail();
        $this->assertSame('completed', $backup->status);
        $this->assertSame($admin->getKey(), $backup->created_by);

        $this->deleteJson("/api/admin/backups/{$backup->getKey()}")->assertOk();
        $this->assertDatabaseMissing('backups', ['id' => $backup->getKey()]);
    }

    public function test_platform_alert_broadcasts_only_to_target_audience(): void
    {
        $this->role('system_admin');
        $admin = $this->systemAdmin();
        $hospital = Hospital::factory()->create();
        $worker = $this->staff($hospital, 'healthcare_worker');

        Sanctum::actingAs($admin);

        $this->postJson('/api/admin/alerts', [
            'audience' => 'healthcare_worker',
            'title' => 'System notice',
            'body' => 'Referral form updated.',
        ])->assertCreated()
            ->assertJsonPath('recipients', 1);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $worker->getKey(),
            'type' => 'platform_alert',
            'title' => 'System notice',
        ]);
        $this->assertDatabaseMissing('notifications', [
            'user_id' => $admin->getKey(),
        ]);
    }

    public function test_platform_alert_does_not_include_system_admin_audience(): void
    {
        $admin = $this->systemAdmin();
        Sanctum::actingAs($admin);

        $this->postJson('/api/admin/alerts', [
            'audience' => 'system_admin',
            'title' => 'Nope',
            'body' => 'Invalid audience.',
        ])->assertUnprocessable();
    }

    public function test_landing_page_renders_seeded_cms_content(): void
    {
        $this->role('system_admin');

        $this->get('/')
            ->assertOk()
            ->assertSee('Connecting Care')
            ->assertSee('Hospitals,')
            ->assertSee('Open the console');
    }

    public function test_announcements_and_faqs_appear_on_landing_page_when_published(): void
    {
        $this->role('system_admin');
        $admin = User::factory()->withRole('system_admin')->create();

        $announcement = Announcement::create([
            'title' => 'Network update',
            'body' => 'Hospital Hub now supports direct transfers.',
            'author_id' => $admin->getKey(),
            'published_at' => now(),
            'is_active' => true,
        ]);

        Faq::create([
            'question' => 'Who can raise a pre-alert?',
            'answer' => 'Any healthcare worker at a network hospital.',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $this->get('/')
            ->assertSee($announcement->title)
            ->assertSee($announcement->body)
            ->assertSee('Who can raise a pre-alert?')
            ->assertSee('Any healthcare worker at a network hospital.');
    }

    public function test_alerts_page_shows_unread_total_from_real_data(): void
    {
        $admin = $this->systemAdmin();
        $hospital = Hospital::factory()->create();
        $worker = $this->staff($hospital, 'healthcare_worker');

        Notification::create([
            'user_id' => $worker->getKey(),
            'type' => 'platform_alert',
            'title' => 'Notice',
            'body' => 'Planned maintenance.',
        ]);

        $this->actingAs($admin)
            ->get('/admin/alerts')
            ->assertOk()
            ->assertSee('Notice');
    }
}
