<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class OtpVerification extends Model
{
    protected $fillable = [
        'user_id',
        'otp_code',
        'phone_number',
        'expires_at',
        'is_verified',
        'verified_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'verified_at' => 'datetime',
        'is_verified' => 'boolean',
    ];

    /**
     * Get the user that owns the OTP verification
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if OTP is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Check if OTP is valid (not expired and not verified)
     */
    public function isValid(): bool
    {
        return !$this->is_verified && !$this->isExpired();
    }

    /**
     * Mark OTP as verified
     */
    public function markAsVerified(): void
    {
        $this->update([
            'is_verified' => true,
            'verified_at' => now(),
        ]);
    }

    /**
     * Generate a 6-digit OTP code
     */
    public static function generateOtp(): string
    {
        return str_pad((string) rand(100000, 999999), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Create a new OTP verification for a user
     */
    public static function createForUser($userId, $phoneNumber, $expiresInMinutes = 10): self
    {
        // Invalidate any existing OTPs for this user
        self::where('user_id', $userId)
            ->where('is_verified', false)
            ->update(['is_verified' => true]);

        return self::create([
            'user_id' => $userId,
            'otp_code' => self::generateOtp(),
            'phone_number' => $phoneNumber,
            'expires_at' => Carbon::now()->addMinutes($expiresInMinutes),
            'is_verified' => false,
        ]);
    }
}
