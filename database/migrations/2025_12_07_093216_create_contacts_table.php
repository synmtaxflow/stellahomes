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
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->string('contact_address')->nullable();
            $table->string('contact_city')->nullable();
            $table->string('contact_phone1')->nullable();
            $table->string('contact_phone2')->nullable();
            $table->string('contact_email1')->nullable();
            $table->string('contact_email2')->nullable();
            $table->string('whatsapp_number')->nullable();
            $table->integer('booking_timeout_hours')->default(24);
            $table->timestamps();
        });
        
        // Insert default record
        DB::table('contacts')->insert([
            'contact_address' => '123 Hostel Street',
            'contact_city' => 'Dar es Salaam, Tanzania',
            'contact_phone1' => '+255 XXX XXX XXX',
            'contact_phone2' => '+255 XXX XXX XXX',
            'contact_email1' => 'info@isackhostel.com',
            'contact_email2' => 'bookings@isackhostel.com',
            'whatsapp_number' => '+255 XXX XXX XXX',
            'booking_timeout_hours' => 24,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};
