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
        // Update enum to include 'terminated' and 'removed' statuses
        DB::statement("ALTER TABLE `students` MODIFY COLUMN `status` ENUM('active', 'inactive', 'graduated', 'pending_payment', 'booked', 'terminated', 'removed') DEFAULT 'active'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to previous enum (but keep existing data)
        DB::statement("ALTER TABLE `students` MODIFY COLUMN `status` ENUM('active', 'inactive', 'graduated', 'pending_payment', 'booked') DEFAULT 'active'");
    }
};
