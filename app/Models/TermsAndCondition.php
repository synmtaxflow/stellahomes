<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TermsAndCondition extends Model
{
    use HasFactory;

    protected $fillable = [
        'content',
        'version',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get active terms and conditions
     */
    public static function getActive()
    {
        return self::where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->first();
    }
}
