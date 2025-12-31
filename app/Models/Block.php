<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Block extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'image',
        'type',
        'floors',
    ];

    protected $casts = [
        'floors' => 'integer',
    ];

    public function rooms()
    {
        return $this->hasMany(Room::class);
    }
}
