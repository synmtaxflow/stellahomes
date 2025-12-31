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
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('block_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('image')->nullable();
            $table->string('location')->nullable();
            $table->boolean('has_beds')->default(false);
            $table->decimal('price', 10, 2)->nullable();
            $table->enum('duration', ['per_month', 'per_semester'])->nullable();
            $table->string('required')->nullable(); // e.g., "Every 3 months", "Per semester"
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
