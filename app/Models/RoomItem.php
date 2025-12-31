<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RoomItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_id',
        'item_name',
    ];

    public function room()
    {
        return $this->belongsTo(Room::class);
    }
}
