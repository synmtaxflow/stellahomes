<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OwnerDetail extends Model
{
    protected $fillable = [
        'user_id',
        'phone_number',
        'account_number',
        'account_name',
        'bank_name',
        'profile_image',
        'address',
        'additional_info',
    ];

    /**
     * Get the user that owns the detail
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
