<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('payments')) {
            // Check and add period_code
            if (!Schema::hasColumn('payments', 'period_code')) {
                DB::statement('ALTER TABLE payments ADD COLUMN period_code VARCHAR(255) NULL AFTER reference_number');
            }
            
            // Check and add period_start_date
            if (!Schema::hasColumn('payments', 'period_start_date')) {
                DB::statement('ALTER TABLE payments ADD COLUMN period_start_date DATE NULL');
            }
            
            // Check and add period_end_date
            if (!Schema::hasColumn('payments', 'period_end_date')) {
                DB::statement('ALTER TABLE payments ADD COLUMN period_end_date DATE NULL');
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['period_code', 'period_start_date', 'period_end_date']);
        });
    }
};
