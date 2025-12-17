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
            if (!Schema::hasColumn('beds', 'rent_price')) {
                $table->decimal('rent_price', 10, 2)->nullable()->after('name');
            }
            if (!Schema::hasColumn('beds', 'rent_duration')) {
                $table->enum('rent_duration', ['monthly', 'semester'])->nullable()->after('rent_price');
            }
            if (!Schema::hasColumn('beds', 'semester_months')) {
                $table->integer('semester_months')->nullable()->after('rent_duration');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('beds', function (Blueprint $table) {
            if (Schema::hasColumn('beds', 'semester_months')) {
                $table->dropColumn('semester_months');
            }
            if (Schema::hasColumn('beds', 'rent_duration')) {
                $table->dropColumn('rent_duration');
            }
            if (Schema::hasColumn('beds', 'rent_price')) {
                $table->dropColumn('rent_price');
            }
        });
    }
};
