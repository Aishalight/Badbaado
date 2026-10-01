<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Every index here backs a query the console runs on each page load: tenant
 * scoping, status/urgency filters, the trend bucketing, and the audit trail.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->index(['hospital_id', 'role_id'], 'users_hospital_role_index');
        });

        Schema::table('referrals', function (Blueprint $table) {
            $table->index('created_at', 'referrals_created_at_index');
            $table->index(['receiving_hospital_id', 'status'], 'referrals_receiving_status_index');
            $table->index(['referring_hospital_id', 'status'], 'referrals_referring_status_index');
        });

        Schema::table('audit_logs', function (Blueprint $table) {
            $table->index('action', 'audit_logs_action_index');
            $table->index('created_at', 'audit_logs_created_at_index');
            $table->index(['action', 'created_at'], 'audit_logs_action_created_index');
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->index(['user_id', 'read_at'], 'notifications_user_read_index');
        });
    }

    public function down(): void
    {
        Schema::table('notifications', fn (Blueprint $table) => $table->dropIndex('notifications_user_read_index'));
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropIndex('audit_logs_action_created_index');
            $table->dropIndex('audit_logs_created_at_index');
            $table->dropIndex('audit_logs_action_index');
        });
        Schema::table('referrals', function (Blueprint $table) {
            $table->dropIndex('referrals_referring_status_index');
            $table->dropIndex('referrals_receiving_status_index');
            $table->dropIndex('referrals_created_at_index');
        });
        Schema::table('users', fn (Blueprint $table) => $table->dropIndex('users_hospital_role_index'));
    }
};
