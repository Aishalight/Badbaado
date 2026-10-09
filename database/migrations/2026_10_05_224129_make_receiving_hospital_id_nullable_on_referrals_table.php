<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * A referral can now be addressed straight to an independent doctor, in
     * which case there is no receiving facility at all. The referring hospital
     * deliberately stays NOT NULL: every active account belongs to a facility
     * (their own practice for independent doctors), so a referral always has a
     * tenant on the sending side.
     */
    public function up(): void
    {
        Schema::table('referrals', function (Blueprint $table): void {
            $table->unsignedBigInteger('receiving_hospital_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $orphaned = DB::table('referrals')->whereNull('receiving_hospital_id')->count();

        if ($orphaned > 0) {
            throw new RuntimeException(
                "Cannot restore the NOT NULL receiving_hospital_id: {$orphaned} referral(s) are addressed "
                .'directly to a doctor and have no receiving facility to fall back to.'
            );
        }

        Schema::table('referrals', function (Blueprint $table): void {
            $table->unsignedBigInteger('receiving_hospital_id')->nullable(false)->change();
        });
    }
};
