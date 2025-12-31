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
        // Update existing 'manual' values to 'cash'
        DB::table('payments')
            ->where('payment_method', 'manual')
            ->update(['payment_method' => 'cash']);

        // Modify the enum column
        DB::statement("ALTER TABLE payments MODIFY COLUMN payment_method ENUM('cash', 'bank') DEFAULT 'cash'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert 'cash' back to 'manual'
        DB::table('payments')
            ->where('payment_method', 'cash')
            ->update(['payment_method' => 'manual']);

        // Revert the enum column
        DB::statement("ALTER TABLE payments MODIFY COLUMN payment_method ENUM('cash', 'automatic') DEFAULT 'cash'");
    }
};
