<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'amount',
        'reserve_amount',
        'payment_date',
        'payment_method',
        'status',
        'reference_number',
        'merchant_reference',
        'notes',
        'period_code',
        'period_start_date',
        'period_end_date',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'reserve_amount' => 'decimal:2',
        'payment_date' => 'date',
        'period_start_date' => 'date',
        'period_end_date' => 'date',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
