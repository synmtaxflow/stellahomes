<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Student;
use App\Models\Bed;
use App\Models\Room;
use App\Models\RentSchedule;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class PaymentController extends Controller
{
    public function index()
    {
        // Get latest payment for each student
        $payments = Payment::with(['student.room.block', 'student.bed'])
            ->select('payments.*')
            ->join(DB::raw('(SELECT student_id, MAX(payment_date) as max_date, MAX(created_at) as max_created 
                           FROM payments GROUP BY student_id) as latest'), function($join) {
                $join->on('payments.student_id', '=', 'latest.student_id')
                     ->on('payments.payment_date', '=', 'latest.max_date')
                     ->on('payments.created_at', '=', 'latest.max_created');
            })
            ->latest('payments.payment_date')
            ->latest('payments.created_at')
            ->get();
        
        return view('payments.index', compact('payments'));
    }

    public function manualPayments()
    {
        $students = Student::with(['room.block', 'bed'])
            ->where('status', 'active')
            ->whereNull('check_out_date')
            ->latest()
            ->get();
        
        // Get latest payment for each student (both cash and bank payments)
        $studentIds = Payment::distinct()
            ->pluck('student_id');
        
        $payments = collect();
        
        foreach ($studentIds as $studentId) {
            // Get latest payment regardless of payment method (cash or bank)
            $latestPayment = Payment::with(['student.room.block', 'student.bed'])
                ->where('student_id', $studentId)
                ->orderBy('payment_date', 'desc')
                ->orderBy('created_at', 'desc')
                ->first();
            
            if ($latestPayment) {
                $payments->push($latestPayment);
            }
        }
        
        // Sort by payment date (latest first)
        $payments = $payments->sortByDesc(function($payment) {
            return $payment->payment_date . ' ' . $payment->created_at;
        })->values();
        
        return view('payments.manual', compact('students', 'payments'));
    }

    public function store(Request $request)
    {
        // Reference number is required for bank payments, optional for cash
        $rules = [
            'student_id' => 'required|exists:students,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:cash,bank',
            'notes' => 'nullable|string',
            'check_in_date' => 'nullable|date', // Optional - will use from student record if not provided
        ];
        
        // Add reference_number validation based on payment method
        if ($request->payment_method === 'bank') {
            $rules['reference_number'] = 'required|string|max:255';
        } else {
            $rules['reference_number'] = 'nullable|string|max:255';
        }
        
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed. Please check your input.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $student = Student::with(['bed', 'room'])->findOrFail($request->student_id);
            
            // Determine rent price and rent details based on bed or room
            $rentPrice = 0; // Base rent price per month (for monthly) or per semester (for semester)
            $rentDuration = null;
            $semesterMonths = null;
            $paymentFrequency = null;
            
            if ($student->bed_id) {
                $bed = $student->bed;
                $rentPrice = $bed->rent_price ?? 0;
                $rentDuration = $bed->rent_duration;
                $semesterMonths = $bed->semester_months;
                $paymentFrequency = $bed->payment_frequency;
            } elseif ($student->room_id) {
                $room = $student->room;
                $rentPrice = $room->rent_price ?? 0;
                $rentDuration = $room->rent_duration;
                $semesterMonths = $room->semester_months;
                $paymentFrequency = $room->payment_frequency;
            }
            
            // Calculate expected amount for display purposes only (based on frequency)
            // But period calculation uses rent_price directly
            $expectedAmount = 0;
            if ($rentDuration === 'monthly' && $paymentFrequency) {
                $frequencyMonths = $this->getFrequencyMonths($paymentFrequency);
                $expectedAmount = $rentPrice * ($frequencyMonths ?? 1);
            } elseif ($rentDuration === 'semester') {
                $expectedAmount = $rentPrice;
            } else {
                $expectedAmount = $rentPrice;
            }

            // Get student's last payment to retrieve reserve amount
            $lastPayment = Payment::where('student_id', $student->id)
                ->where('status', 'completed')
                ->orderBy('payment_date', 'desc')
                ->orderBy('created_at', 'desc')
                ->first();
            
            // Get previous reserve amount (from last payment) or 0 if no previous payment
            $previousReserveAmount = $lastPayment ? ($lastPayment->reserve_amount ?? 0) : 0;
            
            // Calculate total available amount (previous reserve + new payment)
            $totalAvailable = $previousReserveAmount + $request->amount;
            
            // Calculate payment period (start and end dates)
            // For first payment, use check-in date. For subsequent payments, start from last payment end date
            $lastPaymentWithPeriod = Payment::where('student_id', $student->id)
                ->where('status', 'completed')
                ->whereNotNull('period_end_date')
                ->orderBy('period_end_date', 'desc')
                ->orderBy('payment_date', 'desc')
                ->orderBy('created_at', 'desc')
                ->first();
            
            // Always set period start date
            if ($lastPaymentWithPeriod && $lastPaymentWithPeriod->period_end_date) {
                // For subsequent payments: Start from where last payment ended + 1 day
                $periodStartDate = Carbon::parse($lastPaymentWithPeriod->period_end_date)->addDay();
            } else {
                // First payment - use check-in date from student record (set during booking)
                if ($student->check_in_date) {
                    // Use check-in date from booking
                    $periodStartDate = Carbon::parse($student->check_in_date);
                } elseif ($request->has('check_in_date') && $request->check_in_date) {
                    // Fallback: if check-in date provided in request (shouldn't happen normally)
                    $periodStartDate = Carbon::parse($request->check_in_date);
                    // Update student's check-in date if not already set
                    if (!$student->check_in_date) {
                        $student->update(['check_in_date' => $request->check_in_date]);
                    }
                } else {
                    // Final fallback: use payment date (if no check-in date was set during booking)
                    $periodStartDate = Carbon::parse($request->payment_date);
                }
            }
            
            // Initialize period variables
            $periodEndDate = $periodStartDate->copy(); // Default to start date
            $periodCode = 'PAY-' . strtoupper(substr(md5($request->student_id . $request->payment_date . time()), 0, 8));
            
            // Calculate payment amount to record and reserve amount
            $paymentAmountToRecord = 0;
            $newReserveAmount = 0;
            
            if ($rentPrice > 0 && $totalAvailable > 0) {
                try {
                    // Calculate how many periods can be covered with total available amount
                    if ($rentDuration === 'semester' && $semesterMonths && $semesterMonths > 0) {
                        // For semester: rent_price is per semester
                        $semestersCovered = floor($totalAvailable / $rentPrice);
                        if ($semestersCovered > 0) {
                            $paymentAmountToRecord = $rentPrice * $semestersCovered;
                            $newReserveAmount = $totalAvailable - $paymentAmountToRecord;
                            $periodEndDate = $periodStartDate->copy()->addMonths($semesterMonths * $semestersCovered);
                        } else {
                            // If total available is less than one semester, record nothing and keep as reserve
                            $paymentAmountToRecord = 0;
                            $newReserveAmount = $totalAvailable;
                            $periodEndDate = $periodStartDate->copy()->addMonth();
                        }
                    } else {
                        // For monthly: rent_price is per month
                        $monthsCovered = floor($totalAvailable / $rentPrice);
                        if ($monthsCovered > 0) {
                            $paymentAmountToRecord = $rentPrice * $monthsCovered;
                            $newReserveAmount = $totalAvailable - $paymentAmountToRecord;
                            $periodEndDate = $periodStartDate->copy()->addMonths($monthsCovered);
                        } else {
                            // If total available is less than one month, record nothing and keep as reserve
                            $paymentAmountToRecord = 0;
                            $newReserveAmount = $totalAvailable;
                            $periodEndDate = $periodStartDate->copy()->addMonth();
                        }
                    }
                } catch (\Exception $e) {
                    Log::error('Error calculating payment amount and reserve: ' . $e->getMessage());
                    // Default: record nothing, keep all as reserve
                    $paymentAmountToRecord = 0;
                    $newReserveAmount = $totalAvailable;
                    $periodEndDate = $periodStartDate->copy()->addMonth();
                }
            } else {
                // If no rent price, keep all as reserve
                $paymentAmountToRecord = 0;
                $newReserveAmount = $totalAvailable;
                $periodEndDate = $periodStartDate->copy()->addMonth();
            }
            
            // Create payment
            // If payment amount to record is 0, we still record the payment to track reserve amount
            // but we don't create a period (period_start_date and period_end_date remain null)
            $paymentData = [
                'student_id' => $request->student_id,
                'amount' => $paymentAmountToRecord, // Record only the rent price amount
                'reserve_amount' => $newReserveAmount, // Store the new reserve amount
                'payment_date' => $request->payment_date,
                'payment_method' => $request->payment_method,
                'status' => 'completed', // Cash payments are completed immediately
                'reference_number' => $request->reference_number ?? null,
                'notes' => $request->notes ?? null,
            ];
            
            // Only add period information if we're recording a rent payment (amount > 0)
            if ($paymentAmountToRecord > 0) {
                $paymentData['period_code'] = $periodCode;
                $paymentData['period_start_date'] = $periodStartDate ? $periodStartDate->format('Y-m-d') : $request->payment_date;
                $paymentData['period_end_date'] = $periodEndDate ? $periodEndDate->format('Y-m-d') : null;
            }
            
            $payment = Payment::create($paymentData);

            // Update bed status to occupied if payment is completed and bed exists
            if ($payment->status === 'completed' && $student->bed_id) {
                $bed = Bed::find($student->bed_id);
                if ($bed && $bed->status === 'pending_payment') {
                    $bed->update(['status' => 'occupied']);
                }
            }

            DB::commit();

            // Send SMS notification to student about payment
            try {
                $smsService = new SmsService();
                $paymentMethodText = $request->payment_method === 'cash' ? 'Cash' : 'Bank';
                $periodInfo = '';
                
                if ($periodStartDate && $periodEndDate) {
                    $startDateFormatted = Carbon::parse($periodStartDate)->format('d/m/Y');
                    $endDateFormatted = Carbon::parse($periodEndDate)->format('d/m/Y');
                    $periodInfo = "Kipindi: {$startDateFormatted} - {$endDateFormatted}. ";
                }
                
                $totalPaid = Payment::where('student_id', $student->id)
                    ->where('status', 'completed')
                    ->sum('amount');
                
                $pendingAmount = max(0, $expectedAmount - $totalPaid);
                
                $message = "Habari {$student->full_name}. Malipo yako yamepokelewa kwa ufanisi!\n";
                $message .= "Kiasi kilicholipwa: Tsh " . number_format($request->amount, 0) . "\n";
                if ($paymentAmountToRecord > 0) {
                    $message .= "Kiasi kilichorekodiwa (rent): Tsh " . number_format($paymentAmountToRecord, 0) . "\n";
                }
                if ($newReserveAmount > 0) {
                    $message .= "Reserve amount (salio): Tsh " . number_format($newReserveAmount, 0) . "\n";
                }
                $message .= "Njia: {$paymentMethodText}\n";
                $message .= "Tarehe: " . Carbon::parse($request->payment_date)->format('d/m/Y') . "\n";
                if ($periodInfo) {
                    $message .= $periodInfo;
                }
                $message .= "Jumla yaliyolipwa (rent): Tsh " . number_format($totalPaid, 0) . "\n";
                $message .= "Balance yako (reserve): Tsh " . number_format($newReserveAmount, 0);
                
                $smsService->sendSms($student->phone, $message);
            } catch (\Exception $e) {
                // Log SMS error but don't fail the payment
                Log::error('Failed to send payment SMS to student: ' . $e->getMessage(), [
                    'student_id' => $student->id,
                    'payment_id' => $payment->id,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Payment recorded successfully!',
                'payment' => $payment->load(['student.room.block', 'student.bed']),
                'expected_amount' => $expectedAmount
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating payment: ' . $e->getMessage(), [
                'request' => $request->all(),
                'exception' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error recording payment: ' . $e->getMessage(),
                'error_details' => config('app.debug') ? [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString()
                ] : null
            ], 500);
        }
    }

    public function show(Payment $payment)
    {
        return response()->json($payment->load(['student.room.block', 'student.bed']));
    }

    public function update(Request $request, Payment $payment)
    {
        // Reference number is required for bank payments, optional for cash
        $rules = [
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:cash,bank',
            'notes' => 'nullable|string',
        ];
        
        // Add reference_number validation based on payment method
        if ($request->payment_method === 'bank') {
            $rules['reference_number'] = 'required|string|max:255';
        } else {
            $rules['reference_number'] = 'nullable|string|max:255';
        }
        
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed. Please check your input.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $student = Student::with(['bed', 'room'])->findOrFail($payment->student_id);
            
            // Determine expected amount and rent details based on bed or room
            $rentPrice = 0; // Base rent price (per month for monthly, or per semester for semester)
            $rentDuration = null;
            $semesterMonths = null;
            $paymentFrequency = null;
            
            if ($student->bed_id) {
                $bed = $student->bed;
                $rentPrice = $bed->rent_price ?? 0;
                $rentDuration = $bed->rent_duration;
                $semesterMonths = $bed->semester_months;
                $paymentFrequency = $bed->payment_frequency;
            } elseif ($student->room_id) {
                $room = $student->room;
                $rentPrice = $room->rent_price ?? 0;
                $rentDuration = $room->rent_duration;
                $semesterMonths = $room->semester_months;
                $paymentFrequency = $room->payment_frequency;
            }
            
            // Calculate expected amount based on rent duration and payment frequency
            $expectedAmount = 0;
            if ($rentDuration === 'monthly' && $paymentFrequency) {
                $frequencyMonths = $this->getFrequencyMonths($paymentFrequency);
                $expectedAmount = $rentPrice * ($frequencyMonths ?? 1);
            } elseif ($rentDuration === 'semester') {
                $expectedAmount = $rentPrice;
            } else {
                $expectedAmount = $rentPrice;
            }

            // Get active rent schedule for entire hostel (optional - table might not exist)
            $rentSchedule = null;
            try {
                if (Schema::hasTable('rent_schedules')) {
                    // Get the active rent schedule for the entire hostel
                    $rentSchedule = RentSchedule::where('is_active', true)->first();
                }
            } catch (\Exception $e) {
                // Table doesn't exist or other error - continue without schedule
                Log::warning('Rent schedule table not available: ' . $e->getMessage());
                $rentSchedule = null;
            }

            // Calculate payment period (start and end dates) using rent schedule
            // For update, check if this is the first payment or continue from previous
            $previousPayments = Payment::where('student_id', $student->id)
                ->where('status', 'completed')
                ->where('id', '!=', $payment->id)
                ->orderBy('payment_date', 'desc')
                ->orderBy('created_at', 'desc')
                ->get();
            
            if ($previousPayments->isEmpty()) {
                // This is the first payment - use check-in date if available, otherwise rent schedule (if active)
                if ($student->check_in_date) {
                    $periodStartDate = Carbon::parse($student->check_in_date);
                } else {
                    // Only use rent schedule if it's active
                    if ($rentSchedule && $rentSchedule->is_active) {
                        $periodStartDate = $this->calculateRentStartDate($rentSchedule, $request->payment_date, $student);
                    } else {
                        // No active schedule and no check-in date - use payment date
                        $periodStartDate = Carbon::parse($request->payment_date);
                    }
                }
            } else {
                // Check if there's a payment before this one
                $paymentBeforeThis = Payment::where('student_id', $student->id)
                    ->where('status', 'completed')
                    ->where('id', '!=', $payment->id)
                    ->where(function($query) use ($request, $payment) {
                        $query->where('payment_date', '<', $request->payment_date)
                              ->orWhere(function($q) use ($request, $payment) {
                                  $q->where('payment_date', '=', $request->payment_date)
                                    ->where('created_at', '<', $payment->created_at);
                              });
                    })
                    ->orderBy('payment_date', 'desc')
                    ->orderBy('created_at', 'desc')
                    ->first();
                
                if ($paymentBeforeThis && $paymentBeforeThis->period_end_date) {
                    // Continue from previous payment end date + 1 day
                    $periodStartDate = Carbon::parse($paymentBeforeThis->period_end_date)->addDay();
                } else {
                    // Use check-in date if available, otherwise rent schedule (if active)
                    if ($student->check_in_date) {
                        $periodStartDate = Carbon::parse($student->check_in_date);
                    } else {
                        if ($rentSchedule && $rentSchedule->is_active) {
                            $periodStartDate = $this->calculateRentStartDate($rentSchedule, $request->payment_date, $student);
                        } else {
                            $periodStartDate = Carbon::parse($request->payment_date);
                        }
                    }
                }
            }
            
            $periodEndDate = null;
            $periodCode = $payment->period_code ?? 'PAY-' . strtoupper(substr(md5($payment->student_id . $request->payment_date . time()), 0, 8));
            
            if ($rentPrice > 0) {
                // Calculate end date based on rent duration
                // rent_price is always per month (for monthly) or per semester (for semester)
                if ($rentDuration === 'semester' && $semesterMonths) {
                    // For semester: rent_price is per semester
                    $semestersCovered = floor($request->amount / $rentPrice);
                    $periodEndDate = $periodStartDate->copy()->addMonths($semesterMonths * $semestersCovered);
                } else {
                    // For monthly: rent_price is per month
                    // Calculate how many months the payment covers
                    $monthsCovered = floor($request->amount / $rentPrice);
                    $periodEndDate = $periodStartDate->copy()->addMonths($monthsCovered);
                }
            }

            // Update payment
            $payment->update([
                'amount' => $request->amount,
                'payment_date' => $request->payment_date,
                'payment_method' => $request->payment_method,
                'reference_number' => $request->reference_number,
                'notes' => $request->notes,
                'period_code' => $periodCode,
                'period_start_date' => $periodStartDate->format('Y-m-d'),
                'period_end_date' => $periodEndDate ? $periodEndDate->format('Y-m-d') : null,
            ]);

            DB::commit();

            // Send SMS notification to student about payment update
            try {
                $smsService = new SmsService();
                $paymentMethodText = $request->payment_method === 'cash' ? 'Cash' : 'Bank';
                $periodInfo = '';
                
                if ($periodStartDate && $periodEndDate) {
                    $startDateFormatted = Carbon::parse($periodStartDate)->format('d/m/Y');
                    $endDateFormatted = Carbon::parse($periodEndDate)->format('d/m/Y');
                    $periodInfo = "Kipindi: {$startDateFormatted} - {$endDateFormatted}. ";
                }
                
                $totalPaid = Payment::where('student_id', $student->id)
                    ->where('status', 'completed')
                    ->sum('amount');
                
                $expectedAmount = $rentPrice * ($rentDuration === 'semester' ? $semesterMonths : 1);
                $pendingAmount = max(0, $expectedAmount - $totalPaid);
                
                $message = "Habari {$student->full_name}. Malipo yako yameboreshwa!\n";
                $message .= "Kiasi: Tsh " . number_format($request->amount, 0) . "\n";
                $message .= "Njia: {$paymentMethodText}\n";
                $message .= "Tarehe: " . Carbon::parse($request->payment_date)->format('d/m/Y') . "\n";
                if ($periodInfo) {
                    $message .= $periodInfo;
                }
                $message .= "Jumla yaliyolipwa: Tsh " . number_format($totalPaid, 0) . "\n";
                if ($pendingAmount > 0) {
                    $message .= "Deni: Tsh " . number_format($pendingAmount, 0);
                } else {
                    $message .= "Huna deni. Asante!";
                }
                
                $smsService->sendSms($student->phone, $message);
            } catch (\Exception $e) {
                // Log SMS error but don't fail the payment update
                Log::error('Failed to send payment update SMS to student: ' . $e->getMessage(), [
                    'student_id' => $student->id,
                    'payment_id' => $payment->id,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Payment updated successfully!',
                'payment' => $payment->load(['student.room.block', 'student.bed']),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating payment: ' . $e->getMessage(), [
                'request' => $request->all(),
                'exception' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Payment $payment)
    {
        try {
            $payment->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Payment deleted successfully!',
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting payment: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getStudentDetails($studentId)
    {
        try {
            $student = Student::with(['room.block', 'bed'])->findOrFail($studentId);
            
            // Get expected amount and rent details
            $expectedAmount = 0;
            $paymentType = '';
            $rentDuration = null;
            $semesterMonths = null;
            $paymentFrequency = null;
            $rentStartDate = null;
            $rentEndDate = null;
            $nextPaymentDue = null;
            
            if ($student->bed_id) {
                $bed = $student->bed;
                $rentPrice = $bed->rent_price ?? 0;
                $paymentType = 'Bed: ' . $bed->name;
                $rentDuration = $bed->rent_duration;
                $semesterMonths = $bed->semester_months;
                $paymentFrequency = $bed->payment_frequency;
                
                // Calculate expected amount based on rent duration and payment frequency
                if ($rentDuration === 'monthly' && $paymentFrequency) {
                    $frequencyMonths = $this->getFrequencyMonths($paymentFrequency);
                    $expectedAmount = $rentPrice * ($frequencyMonths ?? 1);
                } elseif ($rentDuration === 'semester') {
                    $expectedAmount = $rentPrice;
                } else {
                    $expectedAmount = $rentPrice;
                }
                
                // Calculate rent period
                if ($student->check_in_date) {
                    $rentStartDate = $student->check_in_date;
                    
                    if ($rentDuration === 'semester' && $semesterMonths) {
                        $rentEndDate = $rentStartDate->copy()->addMonths($semesterMonths);
                    } elseif ($rentDuration === 'monthly') {
                        // For monthly, calculate based on payment frequency
                        $frequencyMonths = $this->getFrequencyMonths($paymentFrequency);
                        if ($frequencyMonths) {
                            $rentEndDate = $rentStartDate->copy()->addMonths($frequencyMonths);
                        }
                    }
                }
            } elseif ($student->room_id) {
                $room = $student->room;
                $rentPrice = $room->rent_price ?? 0;
                $paymentType = 'Room: ' . $room->name;
                $rentDuration = $room->rent_duration;
                $semesterMonths = $room->semester_months;
                $paymentFrequency = $room->payment_frequency;
                
                // Calculate expected amount based on rent duration and payment frequency
                if ($rentDuration === 'monthly' && $paymentFrequency) {
                    $frequencyMonths = $this->getFrequencyMonths($paymentFrequency);
                    $expectedAmount = $rentPrice * ($frequencyMonths ?? 1);
                } elseif ($rentDuration === 'semester') {
                    $expectedAmount = $rentPrice;
                } else {
                    $expectedAmount = $rentPrice;
                }
                
                // Calculate rent period
                if ($student->check_in_date) {
                    $rentStartDate = $student->check_in_date;
                    
                    if ($rentDuration === 'semester' && $semesterMonths) {
                        $rentEndDate = $rentStartDate->copy()->addMonths($semesterMonths);
                    } elseif ($rentDuration === 'monthly') {
                        // For monthly, calculate based on payment frequency
                        $frequencyMonths = $this->getFrequencyMonths($paymentFrequency);
                        if ($frequencyMonths) {
                            $rentEndDate = $rentStartDate->copy()->addMonths($frequencyMonths);
                        }
                    }
                }
            }

            // Get total paid amount
            $totalPaid = Payment::where('student_id', $studentId)
                ->where('status', 'completed')
                ->sum('amount');

            // Get pending amount
            $pendingAmount = max(0, $expectedAmount - $totalPaid);
            
            // Check if student has any previous payments
            $hasPreviousPayments = Payment::where('student_id', $studentId)
                ->where('status', 'completed')
                ->exists();
            
            // If student has no previous payments, use check-in date as rent start date
            if (!$hasPreviousPayments) {
                // Use check-in date if available
                if ($student->check_in_date) {
                    $rentStartDate = Carbon::parse($student->check_in_date);
                    
                    // Recalculate rent end date based on check-in date
                    if ($rentDuration === 'semester' && $semesterMonths) {
                        $rentEndDate = $rentStartDate->copy()->addMonths($semesterMonths);
                    } elseif ($rentDuration === 'monthly' && $paymentFrequency) {
                        $frequencyMonths = $this->getFrequencyMonths($paymentFrequency);
                        if ($frequencyMonths) {
                            $rentEndDate = $rentStartDate->copy()->addMonths($frequencyMonths);
                        }
                    }
                } else {
                    // Fallback: Check if there's an active rent schedule
                    $rentSchedule = null;
                    try {
                        if (Schema::hasTable('rent_schedules')) {
                            $rentSchedule = RentSchedule::where('is_active', true)->first();
                        }
                    } catch (\Exception $e) {
                        Log::warning('Rent schedule table not available: ' . $e->getMessage());
                    }
                    
                    // Only use rent schedule if it's active
                    if ($rentSchedule && $rentSchedule->is_active) {
                        $today = Carbon::today();
                        $calculatedStartDate = $this->calculateRentStartDate($rentSchedule, $today->format('Y-m-d'), $student);
                        
                        if ($calculatedStartDate) {
                            $rentStartDate = $calculatedStartDate;
                            
                            if ($rentDuration === 'semester' && $semesterMonths) {
                                $rentEndDate = $rentStartDate->copy()->addMonths($semesterMonths);
                            } elseif ($rentDuration === 'monthly' && $paymentFrequency) {
                                $frequencyMonths = $this->getFrequencyMonths($paymentFrequency);
                                if ($frequencyMonths) {
                                    $rentEndDate = $rentStartDate->copy()->addMonths($frequencyMonths);
                                }
                            }
                        }
                    }
                    // If no active schedule and no check-in date, rentStartDate remains null
                }
            }
            
            // Calculate next payment due date based on last payment
            $lastPayment = Payment::where('student_id', $studentId)
                ->where('status', 'completed')
                ->orderBy('payment_date', 'desc')
                ->orderBy('created_at', 'desc')
                ->first();
            
            if ($lastPayment && $rentDuration === 'monthly' && $paymentFrequency) {
                $frequencyMonths = $this->getFrequencyMonths($paymentFrequency);
                if ($frequencyMonths) {
                    $nextPaymentDue = $lastPayment->payment_date->copy()->addMonths($frequencyMonths);
                }
            }

            // Get all payments for this student (latest first)
            $payments = Payment::where('student_id', $studentId)
                ->orderBy('payment_date', 'desc')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'student' => $student,
                'rent_price' => $rentPrice, // Base rent price per month or per semester
                'expected_amount' => $expectedAmount, // Expected amount based on frequency
                'total_paid' => $totalPaid,
                'pending_amount' => $pendingAmount,
                'payment_type' => $paymentType,
                'rent_duration' => $rentDuration,
                'semester_months' => $semesterMonths,
                'payment_frequency' => $paymentFrequency,
                'rent_start_date' => $rentStartDate ? $rentStartDate->format('Y-m-d') : null,
                'rent_end_date' => $rentEndDate ? $rentEndDate->format('Y-m-d') : null,
                'next_payment_due' => $nextPaymentDue ? $nextPaymentDue->format('Y-m-d') : null,
                'payments' => $payments,
                'has_previous_payments' => $hasPreviousPayments,
                'check_in_date' => $student->check_in_date ? $student->check_in_date->format('Y-m-d') : null,
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching student details: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching student details: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getStudentPayments($studentId)
    {
        try {
            $payments = Payment::where('student_id', $studentId)
                ->orderBy('payment_date', 'desc')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'payments' => $payments,
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching student payments: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching student payments: ' . $e->getMessage()
            ], 500);
        }
    }

    private function getFrequencyMonths($paymentFrequency)
    {
        $frequencyMap = [
            'one_month' => 1,
            'two_months' => 2,
            'three_months' => 3,
            'four_months' => 4,
            'five_months' => 5,
            'six_months' => 6,
        ];
        
        return $frequencyMap[$paymentFrequency] ?? null;
    }

    /**
     * Calculate rent start date based on rent schedule
     * For first payment: If semester schedule, use semester start date unless delay days have passed
     * Note: This method is only called if rent schedule is active
     */
    private function calculateRentStartDate($rentSchedule, $paymentDate, $student)
    {
        $paymentDateCarbon = Carbon::parse($paymentDate);
        
        // Check if this is the first payment for the student
        $hasPreviousPayments = Payment::where('student_id', $student->id)
            ->where('status', 'completed')
            ->exists();
        
        // If no schedule or schedule is inactive, use payment date
        if (!$rentSchedule || !$rentSchedule->is_active) {
            return $paymentDateCarbon;
        }

        switch ($rentSchedule->schedule_type) {
            case 'begin_of_semester':
                // Use semester start month (for all years) if available
                if ($rentSchedule->semester_start_date) {
                    $storedDate = Carbon::parse($rentSchedule->semester_start_date);
                    $semesterMonth = $storedDate->month; // Get month from stored date
                    $semesterDay = $storedDate->day; // Get day from stored date
                    
                    // Build semester start date for the payment year using the stored month and day
                    $semesterYear = $paymentDateCarbon->year;
                    
                    // If payment month is before semester month, semester might be in previous year
                    // Example: Payment in January, but semester starts in December (previous year)
                    if ($paymentDateCarbon->month < $semesterMonth) {
                        $semesterYear = $paymentDateCarbon->year - 1;
                    }
                    
                    $semesterStartDate = Carbon::create($semesterYear, $semesterMonth, $semesterDay);
                    
                    // If payment date is before semester start, use semester start date
                    if ($paymentDateCarbon->lessThan($semesterStartDate)) {
                        return $semesterStartDate;
                    }
                    
                    // Check if delay_days has passed since semester start
                    $delayDays = $rentSchedule->delay_days ?? 15; // Default to 15 days if not set
                    
                    // Calculate days between semester start and payment date
                    // Use startOfDay() to ensure we're comparing dates only (no time component)
                    $semesterStartDateNormalized = $semesterStartDate->copy()->startOfDay();
                    $paymentDateNormalized = $paymentDateCarbon->copy()->startOfDay();
                    
                    // Calculate the difference in days using diff() method for more accurate calculation
                    // Since payment date is after semester start, the difference will be positive
                    $diff = $semesterStartDateNormalized->diff($paymentDateNormalized);
                    $daysSinceSemesterStart = $diff->days;
                    
                    // Alternative calculation: direct timestamp difference
                    // This ensures we get the exact number of days
                    $timestampDiff = $paymentDateNormalized->timestamp - $semesterStartDateNormalized->timestamp;
                    $daysSinceSemesterStartAlt = floor($timestampDiff / 86400); // 86400 seconds in a day
                    
                    // Use the alternative calculation for accuracy
                    $daysSinceSemesterStart = $daysSinceSemesterStartAlt;
                    
                    // Log for debugging (can be removed in production)
                    Log::info('Rent start date calculation', [
                        'semester_start_date' => $semesterStartDate->format('Y-m-d'),
                        'payment_date' => $paymentDateCarbon->format('Y-m-d'),
                        'days_since_semester_start' => $daysSinceSemesterStart,
                        'delay_days' => $delayDays,
                        'use_payment_date' => $daysSinceSemesterStart > $delayDays
                    ]);
                    
                    // If more than delay_days have passed since semester start, use payment date
                    // This means if payment is made after the delay period, rent starts from payment date
                    // Example: Semester starts Dec 1, delay days = 15, payment date = Dec 28
                    // Days since start = 27, which is > 15, so rent starts from Dec 28 (payment date)
                    if ($daysSinceSemesterStart > $delayDays) {
                        return $paymentDateCarbon;
                    }
                    
                    // Otherwise, use semester start date (payment is after semester start but within delay days)
                    // Example: Semester starts Dec 1, delay days = 15, payment date = Dec 10
                    // Days since start = 9, which is <= 15, so rent starts from Dec 1 (semester start)
                    return $semesterStartDate;
                }
                return $paymentDateCarbon;

            case 'first_payment':
                // Check if semester has passed
                if ($rentSchedule->semester_start_date && $rentSchedule->semester_months) {
                    $semesterEndDate = Carbon::parse($rentSchedule->semester_start_date)
                        ->addMonths($rentSchedule->semester_months);
                    
                    // If payment date is after semester end, use payment date
                    // Otherwise, use semester start date
                    if ($paymentDateCarbon->greaterThan($semesterEndDate)) {
                        return $paymentDateCarbon;
                    } else {
                        return Carbon::parse($rentSchedule->semester_start_date);
                    }
                }
                // If no semester info, check if this is first payment
                $firstPayment = Payment::where('student_id', $student->id)
                    ->where('status', 'completed')
                    ->orderBy('payment_date', 'asc')
                    ->orderBy('created_at', 'asc')
                    ->first();
                
                // If this is the first payment, use payment date
                if (!$firstPayment || $firstPayment->id === null) {
                    return $paymentDateCarbon;
                }
                
                // Otherwise, continue from last payment end date
                $lastPayment = Payment::where('student_id', $student->id)
                    ->where('status', 'completed')
                    ->orderBy('payment_date', 'desc')
                    ->orderBy('created_at', 'desc')
                    ->first();
                
                if ($lastPayment && $lastPayment->period_end_date) {
                    // Continue from last payment end date + 1 day
                    return Carbon::parse($lastPayment->period_end_date)->addDay();
                }
                
                return $paymentDateCarbon;

            case 'custom':
                // Use custom start date if available
                if ($rentSchedule->custom_start_date) {
                    return Carbon::parse($rentSchedule->custom_start_date);
                }
                return $paymentDateCarbon;

            default:
                return $paymentDateCarbon;
        }
    }
}
