<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HostelDetail extends Model
{
    protected $fillable = [
        'hostel_name',
        'description',
        'logo',
    ];

    /**
     * Get the single hostel detail record (there should only be one)
     * Always returns the first record, creates if doesn't exist
     */
    public static function getHostelDetail()
    {
        $hostelDetail = self::first();
        
        if (!$hostelDetail) {
            // Create default hostel detail if none exists
            $hostelDetail = self::create([
                'hostel_name' => 'ISACK HOSTEL',
                'description' => null,
                'logo' => null,
            ]);
        }
        
        return $hostelDetail;
    }

    /**
     * Update hostel detail information
     * Always updates the first (and only) record, no duplicates
     */
    public static function updateHostelDetail(array $data)
    {
        $hostelDetail = self::getHostelDetail();
        $hostelDetail->update($data);
        return $hostelDetail;
    }
}
