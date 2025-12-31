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
        Schema::table('student_control_numbers', function (Blueprint $table) {
            // Add starting_balance column to track initial balance (100,000)
            $table->decimal('starting_balance', 10, 2)->default(100000)->after('control_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_control_numbers', function (Blueprint $table) {
            $table->dropColumn('starting_balance');
        });
    }
};
