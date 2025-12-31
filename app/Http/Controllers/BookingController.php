<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Bed;
use App\Models\Room;
use App\Models\Payment;
use App\Models\Contact;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BookingController extends Controller
{
    /**
     * Display pending bookings
     */
    public function index()
    {
        if (Auth::user()->role !== 'owner') {
            return redirect()->back()->with('error', 'Unauthorized action.');
        }

        // Get pending bookings (students with booked status)
        $pendingBookings = Student::with(['room', 'bed'])
            ->where('status', 'booked')
            ->orderBy('created_at', 'desc')
            ->get();

        // Get booking timeout from contacts table
        $contact = Contact::getContact();
        $timeoutHours = $contact->booking_timeout_hours ?? 24;

        // Calculate time remaining for each booking
        $bookings = $pendingBookings->map(function($booking) use ($timeoutHours) {
            $bed = $booking->bed;
            $expiresAt = null;
            $timeRemaining = null;
            $isExpired = false;
            $hoursRemaining = 0;

            if ($bed && $bed->booking_expires_at) {
                $expiresAt = Carbon::parse($bed->booking_expires_at);
                $now = Carbon::now();
                
                if ($expiresAt->isPast()) {
                    $isExpired = true;
                    $timeRemaining = 'Expired';
                    $hoursRemaining = 0;
                } else {
                    $diff = $now->diff($expiresAt);
                    $hoursRemaining = $diff->h + ($diff->days * 24);
                    $minutes = $diff->i;
                    
                    if ($hoursRemaining > 0) {
                        $timeRemaining = $hoursRemaining . ' hour' . ($hoursRemaining > 1 ? 's' : '') . ' ' . $minutes . ' min';
                    } else {
                        $timeRemaining = $minutes . ' minute' . ($minutes > 1 ? 's' : '');
                    }
                }
            } else {
                // For rooms without beds, calculate from created_at + timeout
                $createdAt = Carbon::parse($booking->created_at);
                $expiresAt = $createdAt->copy()->addHours($timeoutHours);
                $now = Carbon::now();
                
                if ($expiresAt->isPast()) {
                    $isExpired = true;
                    $timeRemaining = 'Expired';
                    $hoursRemaining = 0;
                } else {
                    $diff = $now->diff($expiresAt);
                    $hoursRemaining = $diff->h + ($diff->days * 24);
                    $minutes = $diff->i;
                    
                    if ($hoursRemaining > 0) {
                        $timeRemaining = $hoursRemaining . ' hour' . ($hoursRemaining > 1 ? 's' : '') . ' ' . $minutes . ' min';
                    } else {
                        $timeRemaining = $minutes . ' minute' . ($minutes > 1 ? 's' : '');
                    }
                }
            }

            return [
                'id' => $booking->id,
                'student' => $booking,
                'room' => $booking->room,
                'bed' => $bed,
                'expires_at' => $expiresAt,
                'time_remaining' => $timeRemaining,
                'is_expired' => $isExpired,
                'hours_remaining' => $hoursRemaining,
            ];
        });

        return view('bookings.index', compact('bookings', 'timeoutHours'));
    }

    /**
     * Record payment for booking
     */
    public function recordPayment(Request $request, $bookingId)
    {
        if (Auth::user()->role !== 'owner') {
            return redirect()->back()->with('error', 'Unauthorized action.');
        }

        $request->validate([
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,bank',
            'payment_date' => 'required|date',
            'notes' => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();
        try {
            $student = Student::findOrFail($bookingId);
            
            if ($student->status !== 'booked') {
                return redirect()->back()->with('error', 'This booking is not in booked status.');
            }

            // Get rent details
            $rentPrice = 0;
            $rentDuration = null;
            $semesterMonths = null;

            if ($student->bed_id) {
                $bed = $student->bed;
                $rentPrice = $bed->rent_price ?? 0;
                $rentDuration = $bed->rent_duration;
                $semesterMonths = $bed->semester_months;
            } elseif ($student->room_id) {
                $room = $student->room;
                $rentPrice = $room->rent_price ?? 0;
                $rentDuration = $room->rent_duration;
                $semesterMonths = $room->semester_months;
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

            // Calculate period start date (use check-in date for first payment)
            $periodStartDate = null;
            if ($student->check_in_date) {
                $periodStartDate = Carbon::parse($student->check_in_date);
            } else {
                $periodStartDate = Carbon::parse($request->payment_date);
            }

            // Calculate period end date based on rent duration and total available amount
            $periodEndDate = $periodStartDate->copy();
            $periodCode = 'PAY-' . strtoupper(substr(md5($student->id . $request->payment_date . time()), 0, 8));

            // Calculate payment amount to record and reserve amount
            $paymentAmountToRecord = 0;
            $newReserveAmount = 0;

            if ($rentPrice > 0 && $totalAvailable > 0) {
                try {
                    if ($rentDuration === 'semester' && $semesterMonths && $semesterMonths > 0) {
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

            // Create payment record
            // If payment amount to record is 0, we still record the payment to track reserve amount
            // but we don't create a period (period_start_date and period_end_date remain null)
            $paymentData = [
                'student_id' => $student->id,
                'amount' => $paymentAmountToRecord, // Record only the rent price amount
                'reserve_amount' => $newReserveAmount, // Store the new reserve amount
                'payment_date' => $request->payment_date,
                'payment_method' => $request->payment_method,
                'status' => 'completed',
                'notes' => $request->notes,
            ];
            
            // Only add period information if we're recording a rent payment (amount > 0)
            if ($paymentAmountToRecord > 0) {
                $paymentData['period_code'] = $periodCode;
                $paymentData['period_start_date'] = $periodStartDate->format('Y-m-d');
                $paymentData['period_end_date'] = $periodEndDate->format('Y-m-d');
            }
            
            $payment = Payment::create($paymentData);

            // Update student status to active
            $student->update([
                'status' => 'active',
            ]);

            // Update bed status to occupied
            if ($student->bed_id) {
                $bed = Bed::find($student->bed_id);
                if ($bed) {
                    $bed->update([
                        'status' => 'occupied',
                        'booking_expires_at' => null,
                    ]);
                }
            }

            DB::commit();

            // Send SMS to student with payment details
            $smsService = new SmsService();
            $paymentMethodText = $request->payment_method === 'cash' ? 'Cash' : 'Bank';
            
            $message = "Habari {$student->full_name}. Malipo yako yamepokelewa kwa ufanisi!\n";
            $message .= "Kiasi kilicholipwa: Tsh " . number_format($request->amount, 0) . "\n";
            if ($paymentAmountToRecord > 0) {
                $message .= "Kiasi kilichorekodiwa (rent): Tsh " . number_format($paymentAmountToRecord, 0) . "\n";
                $startDateFormatted = $periodStartDate->format('d/m/Y');
                $endDateFormatted = $periodEndDate->format('d/m/Y');
                $message .= "Kipindi: {$startDateFormatted} - {$endDateFormatted}\n";
            }
            if ($newReserveAmount > 0) {
                $message .= "Reserve amount (salio): Tsh " . number_format($newReserveAmount, 0) . "\n";
            }
            $message .= "Njia: {$paymentMethodText}\n";
            $message .= "Tarehe: " . Carbon::parse($request->payment_date)->format('d/m/Y') . "\n";
            $message .= "Booking yako sasa ni active. Karibu ISACK HOSTEL!";
            
            $smsService->sendSms($student->phone, $message);

            return redirect()->route('bookings.index')
                ->with('success', 'Payment recorded successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to record payment: ' . $e->getMessage());
        }
    }

    /**
     * Cancel/Expire booking
     */
    public function cancel($bookingId)
    {
        if (Auth::user()->role !== 'owner') {
            return redirect()->back()->with('error', 'Unauthorized action.');
        }

        DB::beginTransaction();
        try {
            $student = Student::findOrFail($bookingId);
            
            // Free the bed
            if ($student->bed_id) {
                $bed = Bed::find($student->bed_id);
                if ($bed) {
                    $bed->update([
                        'status' => 'free',
                        'booking_expires_at' => null,
                    ]);
                }
            }

            // Delete user account - try multiple methods to find the user
            $user = null;
            
            // Method 1: Try to find by user_id if column exists
            if (isset($student->user_id) && $student->user_id) {
                $user = \App\Models\User::find($student->user_id);
            }
            
            // Method 2: If not found, try to find by email (generated email format)
            if (!$user && $student->email) {
                $user = \App\Models\User::where('email', $student->email)->first();
            }
            
            // Method 3: If still not found, try to find by username (phone number)
            if (!$user && $student->phone) {
                $username = preg_replace('/[^0-9]/', '', $student->phone);
                $user = \App\Models\User::where('username', $username)
                    ->where('role', 'student')
                    ->first();
            }
            
            // Delete the user if found
            if ($user) {
                $user->delete();
            }

            // Delete student record completely (not just update status)
            // This removes the student from the system when booking is cancelled
            $student->delete();

            DB::commit();

            return redirect()->route('bookings.index')
                ->with('success', 'Booking cancelled and student deleted successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to cancel booking: ' . $e->getMessage());
        }
    }
}
