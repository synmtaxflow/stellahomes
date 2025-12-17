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
        Schema::table('rooms', function (Blueprint $table) {
            if (!Schema::hasColumn('rooms', 'payment_frequency')) {
                $table->enum('payment_frequency', ['one_month', 'two_months', 'three_months', 'four_months', 'five_months', 'six_months'])->nullable()->after('semester_months');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            if (Schema::hasColumn('rooms', 'payment_frequency')) {
                $table->dropColumn('payment_frequency');
            }
        });
    }
};
