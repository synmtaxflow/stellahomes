<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Bed extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_id',
        'name',
        'rent_price',
        'rent_duration',
        'semester_months',
        'payment_frequency',
        'status',
        'booking_expires_at',
    ];

    protected $casts = [
        'rent_price' => 'decimal:2',
        'semester_months' => 'integer',
        'booking_expires_at' => 'datetime',
    ];

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function student()
    {
        return $this->hasOne(Student::class);
    }
}
