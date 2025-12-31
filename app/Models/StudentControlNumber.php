<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StudentControlNumber extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'control_number',
        'starting_balance',
        'bill_amount',
        'total_paid',
        'remaining_balance',
        'is_active',
        'is_fully_paid',
        'expires_at',
        'is_expired',
    ];

    protected $casts = [
        'starting_balance' => 'decimal:2',
        'bill_amount' => 'decimal:2',
        'total_paid' => 'decimal:2',
        'remaining_balance' => 'decimal:2',
        'is_active' => 'boolean',
        'is_fully_paid' => 'boolean',
        'expires_at' => 'datetime',
        'is_expired' => 'boolean',
    ];

    /**
     * Get the student that owns the control number
     */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Update remaining balance and payment status
     * For flexible payments: remaining_balance = starting_balance - total_paid
     */
    public function updateBalance()
    {
        // For flexible payments, we use starting_balance instead of bill_amount
        // remaining_balance = starting_balance - total_paid
        if ($this->bill_amount == 0) {
            // Flexible payment system
            $startingBalance = $this->starting_balance ?? 100000;
            $this->remaining_balance = max(0, $startingBalance - $this->total_paid);
        } else {
            // Fixed bill amount system (old way)
            $this->remaining_balance = $this->bill_amount - $this->total_paid;
        }
        
        $this->is_fully_paid = $this->remaining_balance <= 0;
        $this->save();
    }
}
