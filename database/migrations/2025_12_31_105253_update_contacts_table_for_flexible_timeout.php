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
        Schema::table('contacts', function (Blueprint $table) {
            $table->string('booking_timeout_unit', 10)->default('hours')->after('booking_timeout_hours')->comment('hours or minutes');
            $table->integer('booking_timeout_value')->nullable()->after('booking_timeout_unit')->comment('Timeout value in the specified unit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->dropColumn(['booking_timeout_unit', 'booking_timeout_value']);
        });
    }
};
