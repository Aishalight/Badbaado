<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('referrals', function (Blueprint $table) {
            $table->id();
            $table->string('referral_number')->unique();
            $table->foreignId('referring_hospital_id')->constrained('hospitals');
            $table->foreignId('receiving_hospital_id')->constrained('hospitals');
            $table->foreignId('referring_user_id')->constrained('users');
            $table->foreignId('coordinator_user_id')->nullable()->constrained('users');
            $table->foreignId('patient_id')->constrained('patients');
            $table->string('status')->default('draft');
            $table->string('urgency')->nullable();
            $table->boolean('is_emergency')->default(false);
            $table->string('department')->nullable();
            $table->text('referral_reason')->nullable();
            $table->text('symptoms')->nullable();
            $table->json('vitals')->nullable();
            $table->string('consciousness')->nullable();
            $table->boolean('trauma_indicator')->default(false);
            $table->text('existing_conditions')->nullable();
            $table->text('current_interventions')->nullable();
            $table->text('notes')->nullable();
            $table->json('ai_suggestion')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();

            $table->index(['referring_hospital_id', 'status'], 'idx_referrals_from_hosp_status');
            $table->index(['receiving_hospital_id', 'status'], 'idx_referrals_to_hosp_status');
            $table->index(['referring_user_id', 'status'], 'idx_referrals_user_status');
            $table->index(['status', 'urgency'], 'idx_referrals_status_urgency');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referrals');
    }
};
