<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Room extends Model
{
    use HasFactory;

    protected $fillable = [
        'block_id',
        'name',
        'image',
        'location',
        'description',
        'has_beds',
        'price',
        'duration',
        'required',
        'rent_price',
        'rent_duration',
        'semester_months',
        'payment_frequency',
    ];

    protected $casts = [
        'has_beds' => 'boolean',
        'price' => 'decimal:2',
        'rent_price' => 'decimal:2',
        'semester_months' => 'integer',
    ];

    public function block()
    {
        return $this->belongsTo(Block::class);
    }

    public function beds()
    {
        return $this->hasMany(Bed::class);
    }

    public function items()
    {
        return $this->hasMany(RoomItem::class);
    }

    public function students()
    {
        return $this->hasMany(Student::class);
    }
}
