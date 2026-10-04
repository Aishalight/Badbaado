<?php

use App\Enums\NotificationSeverity;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Notifications previously carried a flat `type` string, so a rejected referral
 * looked identical to a routine status change. Severity separates the four
 * delivery tiers the console renders, and lets unread alarms be queried first.
 *
 * The 2026_09_30 index migration also added `notifications_user_read_index` on
 * the same columns as `idx_notifications_user_read`; the redundant copy is
 * dropped here and replaced with a severity-aware index.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->string('severity')->default(NotificationSeverity::INFO->value)->after('type');
        });

        // Backfill so historical rows carry a meaningful tier rather than the default.
        foreach (NotificationSeverity::legacyTypeMap() as $type => $severity) {
            DB::table('notifications')
                ->where('type', $type)
                ->update(['severity' => $severity]);
        }

        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex('notifications_user_read_index');
            $table->index(['user_id', 'severity', 'read_at'], 'notifications_user_severity_read_index');
        });
    }

    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex('notifications_user_severity_read_index');
            $table->index(['user_id', 'read_at'], 'notifications_user_read_index');
            $table->dropColumn('severity');
        });
    }
};
