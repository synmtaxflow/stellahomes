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
        // Update existing 'automatic' values to 'bank'
        DB::table('payments')
            ->where('payment_method', 'automatic')
            ->update(['payment_method' => 'bank']);

        // Modify the enum column
        DB::statement("ALTER TABLE payments MODIFY COLUMN payment_method ENUM('cash', 'bank') DEFAULT 'cash'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert 'bank' back to 'automatic'
        DB::table('payments')
            ->where('payment_method', 'bank')
            ->update(['payment_method' => 'automatic']);

        // Revert the enum column
        DB::statement("ALTER TABLE payments MODIFY COLUMN payment_method ENUM('cash', 'automatic') DEFAULT 'cash'");
    }
};
