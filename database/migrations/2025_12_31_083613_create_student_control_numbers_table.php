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
        Schema::create('student_control_numbers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->string('control_number', 20)->unique();
            $table->decimal('bill_amount', 10, 2)->default(0); // Total amount to pay (rent price)
            $table->decimal('total_paid', 10, 2)->default(0); // Total amount paid so far
            $table->decimal('remaining_balance', 10, 2)->default(0); // Remaining balance
            $table->boolean('is_active')->default(true);
            $table->boolean('is_fully_paid')->default(false);
            $table->timestamps();
            
            $table->index('control_number');
            $table->index('student_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_control_numbers');
    }
};
