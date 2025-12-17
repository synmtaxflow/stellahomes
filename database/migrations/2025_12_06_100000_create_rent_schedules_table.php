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
        Schema::create('rent_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bed_id')->nullable()->constrained('beds')->onDelete('cascade');
            $table->foreignId('room_id')->nullable()->constrained('rooms')->onDelete('cascade');
            $table->enum('schedule_type', ['begin_of_semester', 'first_payment', 'custom'])->default('first_payment');
            $table->date('semester_start_date')->nullable(); // For begin_of_semester type
            $table->date('custom_start_date')->nullable(); // For custom type
            $table->integer('semester_months')->nullable(); // Duration of semester
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Ensure either bed_id or room_id is set, but not both
            $table->index(['bed_id', 'room_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rent_schedules');
    }
};
