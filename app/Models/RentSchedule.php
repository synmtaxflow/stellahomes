<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RentSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'bed_id',
        'room_id',
        'schedule_type',
        'semester_start_date',
        'custom_start_date',
        'semester_months',
        'delay_days',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'semester_start_date' => 'date',
        'custom_start_date' => 'date',
        'semester_months' => 'integer',
        'delay_days' => 'integer',
        'is_active' => 'boolean',
    ];

    public function bed()
    {
        return $this->belongsTo(Bed::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }
}
