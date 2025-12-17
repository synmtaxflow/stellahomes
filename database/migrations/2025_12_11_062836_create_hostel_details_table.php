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
        Schema::create('hostel_details', function (Blueprint $table) {
            $table->id();
            $table->string('hostel_name')->default('ISACK HOSTEL');
            $table->text('description')->nullable();
            $table->string('logo')->nullable();
            $table->timestamps();
        });
        
        // Insert default record
        DB::table('hostel_details')->insert([
            'hostel_name' => 'ISACK HOSTEL',
            'description' => null,
            'logo' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hostel_details');
    }
};
