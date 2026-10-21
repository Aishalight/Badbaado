<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Allow accounts to be deleted even when they created referrals or sent
     * messages.
     *
     * The user reference becomes NULL instead of blocking the delete, so the
     * clinical records themselves — referrals, messages and audit logs — are
     * preserved and the referral lifecycle stays auditable.
     */
    public function up(): void
    {
        $this->makeUserReferencesNullable();

        // SQLite cannot drop a named foreign key, so the ON DELETE action is
        // only rewritten on the drivers that support it. The application
        // clears the references itself before deleting an account, which
        // keeps the delete working on every driver.
        if ($this->canRebuildForeignKeys()) {
            Schema::table('referrals', function (Blueprint $table): void {
                $table->dropForeign('referrals_referring_user_id_foreign');
                $table->foreign('referring_user_id')
                    ->references('id')
                    ->on('users')
                    ->onDelete('set null');
            });

            Schema::table('messages', function (Blueprint $table): void {
                $table->dropForeign('messages_sender_user_id_foreign');
                $table->foreign('sender_user_id')
                    ->references('id')
                    ->on('users')
                    ->onDelete('set null');
            });
        }
    }

    /**
     * Restore the original RESTRICT behaviour.
     */
    public function down(): void
    {
        if ($this->canRebuildForeignKeys()) {
            Schema::table('referrals', function (Blueprint $table): void {
                $table->dropForeign('referrals_referring_user_id_foreign');
                $table->foreign('referring_user_id')
                    ->references('id')
                    ->on('users');
            });

            Schema::table('messages', function (Blueprint $table): void {
                $table->dropForeign('messages_sender_user_id_foreign');
                $table->foreign('sender_user_id')
                    ->references('id')
                    ->on('users');
            });
        }

        // Rows written while the references were nullable would not survive a
        // NOT NULL constraint, so they are given a placeholder author first.
        DB::table('referrals')->whereNull('referring_user_id')->update(['referring_user_id' => 0]);
        DB::table('messages')->whereNull('sender_user_id')->update(['sender_user_id' => 0]);

        Schema::table('referrals', function (Blueprint $table): void {
            $table->foreignId('referring_user_id')->nullable(false)->change();
        });

        Schema::table('messages', function (Blueprint $table): void {
            $table->foreignId('sender_user_id')->nullable(false)->change();
        });
    }

    private function makeUserReferencesNullable(): void
    {
        Schema::table('referrals', function (Blueprint $table): void {
            $table->foreignId('referring_user_id')->nullable()->change();
        });

        Schema::table('messages', function (Blueprint $table): void {
            $table->foreignId('sender_user_id')->nullable()->change();
        });
    }

    /**
     * SQLite has no statement for dropping a foreign key by name.
     */
    private function canRebuildForeignKeys(): bool
    {
        return DB::connection()->getDriverName() !== 'sqlite';
    }
};
