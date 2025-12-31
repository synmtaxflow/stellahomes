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
        Schema::table('beds', function (Blueprint $table) {
            if (!Schema::hasColumn('beds', 'status')) {
                $table->enum('status', ['free', 'occupied', 'pending_payment'])->default('free')->after('payment_frequency');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('beds', function (Blueprint $table) {
            if (Schema::hasColumn('beds', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};
