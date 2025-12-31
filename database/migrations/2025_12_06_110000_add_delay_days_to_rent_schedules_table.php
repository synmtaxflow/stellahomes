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
        if (Schema::hasTable('rent_schedules')) {
            if (!Schema::hasColumn('rent_schedules', 'delay_days')) {
                Schema::table('rent_schedules', function (Blueprint $table) {
                    $table->integer('delay_days')->nullable()->after('semester_months')->comment('Number of days after semester start before rent begins (if exceeded, use payment date)');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('rent_schedules')) {
            if (Schema::hasColumn('rent_schedules', 'delay_days')) {
                Schema::table('rent_schedules', function (Blueprint $table) {
                    $table->dropColumn('delay_days');
                });
            }
        }
    }
};













