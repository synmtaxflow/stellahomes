<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    protected $fillable = [
        'contact_address',
        'contact_city',
        'contact_phone1',
        'contact_phone2',
        'contact_email1',
        'contact_email2',
        'whatsapp_number',
        'booking_timeout_hours',
        'booking_timeout_unit',
        'booking_timeout_value',
    ];

    protected $casts = [
        'booking_timeout_hours' => 'integer',
        'booking_timeout_value' => 'integer',
    ];

    /**
     * Get the single contact record (there should only be one)
     * Always returns the first record, creates if doesn't exist
     */
    public static function getContact()
    {
        $contact = self::first();
        
        if (!$contact) {
            // Create default contact if none exists
            $contact = self::create([
                'contact_address' => '123 Hostel Street',
                'contact_city' => 'Dar es Salaam, Tanzania',
                'contact_phone1' => '+255 XXX XXX XXX',
                'contact_phone2' => '+255 XXX XXX XXX',
                'contact_email1' => 'info@isackhostel.com',
                'contact_email2' => 'bookings@isackhostel.com',
                'whatsapp_number' => '+255 XXX XXX XXX',
                'booking_timeout_hours' => 24,
            ]);
        }
        
        return $contact;
    }

    /**
     * Update contact information
     * Always updates the first (and only) record, no duplicates
     */
    public static function updateContact(array $data)
    {
        $contact = self::getContact();
        $contact->update($data);
        return $contact;
    }
}
