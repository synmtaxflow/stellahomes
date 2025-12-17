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
            if (!Schema::hasColumn('rent_schedules', 'start_day')) {
                Schema::table('rent_schedules', function (Blueprint $table) {
                    $table->integer('start_day')->nullable()->after('delay_days')->comment('Day of month to start rent if semester has already started');
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
            if (Schema::hasColumn('rent_schedules', 'start_day')) {
                Schema::table('rent_schedules', function (Blueprint $table) {
                    $table->dropColumn('start_day');
                });
            }
        }
    }
};
