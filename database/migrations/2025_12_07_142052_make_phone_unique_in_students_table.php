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
        // Remove duplicates first (keep the oldest record)
        DB::statement("
            DELETE s1 FROM students s1
            INNER JOIN students s2 
            WHERE s1.id > s2.id 
            AND s1.phone = s2.phone 
            AND s1.phone IS NOT NULL 
            AND s1.phone != ''
        ");

        // Make phone column unique
        Schema::table('students', function (Blueprint $table) {
            $table->string('phone')->unique()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropUnique(['phone']);
            $table->string('phone')->nullable()->change();
        });
    }
};
