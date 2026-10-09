<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('status')->default('active')->after('role_id')->index();
            $table->foreignId('specialty_id')->nullable()->after('role_id')->constrained('specialties')->nullOnDelete();
        });

        DB::table('users')->where('is_active', true)->update(['status' => 'active']);
        DB::table('users')->where('is_active', false)->update(['status' => 'suspended']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropForeign(['specialty_id']);
            $table->dropColumn(['status', 'specialty_id']);
        });
    }
};
