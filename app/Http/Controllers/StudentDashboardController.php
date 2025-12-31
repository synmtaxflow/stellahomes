<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Payment;
use App\Models\Contact;
use App\Models\StudentControlNumber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class StudentDashboardController extends Controller
{
    /**
     * Display student dashboard
     */
    public function index()
    {
        if (Auth::user()->role !== 'student') {
            return redirect('/dashboard/' . Auth::user()->role);
        }

        $user = Auth::user();
        
        // Find student by username (phone number) or email
        $student = null;
        $username = preg_replace('/[^0-9]/', '', $user->username);
        
        // Try to find student by phone (username is phone number)
        // First try exact match with cleaned username
        $student = Student::where('phone', $username)
            ->orWhere('phone', $user->username)
            ->orWhere('email', $user->email)
            ->with(['room.block', 'bed', 'payments'])
            ->orderBy('created_at', 'desc')
            ->first();
        
        // If still not found, try partial match (in case phone format differs)
        if (!$student) {
            $student = Student::where('phone', 'like', '%' . substr($username, -9) . '%')
                ->with(['room.block', 'bed', 'payments'])
                ->orderBy('created_at', 'desc')
                ->first();
        }

        // Get booking timeout
        $contact = Contact::getContact();
        $timeoutHours = $contact->booking_timeout_hours ?? 24;

        // Calculate booking time remaining if status is 'booked'
        $timeRemaining = null;
        $isExpired = false;
        $hoursRemaining = 0;
        $expiresAt = null;

        if ($student && $student->status === 'booked') {
            $bed = $student->bed;
            
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
                $createdAt = Carbon::parse($student->created_at);
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
        }

        // Get payments with reference numbers (these are the payment codes)
        $payments = [];
        $rentEndDate = null;
        $totalPaid = 0;
        $expectedAmount = 0;
        $balance = 0; // Reserve amount (balance in student's account)
        
        if ($student) {
            $payments = Payment::where('student_id', $student->id)
                ->where('status', 'completed')
                ->orderBy('payment_date', 'desc')
                ->get();
            
            // Calculate total paid (sum of recorded payment amounts)
            $totalPaid = $payments->sum('amount');
            
            // Get last payment with period_end_date
            $lastPayment = Payment::where('student_id', $student->id)
                ->where('status', 'completed')
                ->whereNotNull('period_end_date')
                ->orderBy('period_end_date', 'desc')
                ->first();
            
            if ($lastPayment && $lastPayment->period_end_date) {
                $rentEndDate = Carbon::parse($lastPayment->period_end_date);
            }
            
            // Get the latest payment to get reserve amount (balance)
            $latestPayment = Payment::where('student_id', $student->id)
                ->where('status', 'completed')
                ->orderBy('payment_date', 'desc')
                ->orderBy('created_at', 'desc')
                ->first();
            
            // Balance is the reserve amount from the latest payment
            $balance = $latestPayment ? ($latestPayment->reserve_amount ?? 0) : 0;
            
            // Calculate expected amount and balance
            $rentPrice = 0;
            if ($student->bed_id) {
                $bed = $student->bed;
                $rentPrice = $bed->rent_price ?? 0;
            } elseif ($student->room_id) {
                $room = $student->room;
                $rentPrice = $room->rent_price ?? 0;
            }
            
            // For simplicity, calculate expected for current period
            // In a real system, you'd calculate based on rent duration and time period
            if ($rentPrice > 0) {
                $expectedAmount = $rentPrice; // Monthly or semester amount
            }
        }

        // Get control number for student
        $controlNumber = null;
        if ($student) {
            $controlNumber = StudentControlNumber::where('student_id', $student->id)
                ->where('is_active', true)
                ->first();
        }

        return view('dashboard.student', compact(
            'user',
            'student',
            'payments',
            'timeRemaining',
            'isExpired',
            'hoursRemaining',
            'expiresAt',
            'rentEndDate',
            'totalPaid',
            'expectedAmount',
            'balance',
            'controlNumber'
        ));
    }

    /**
     * Upload profile picture
     */
    public function uploadProfilePicture(Request $request)
    {
        try {
            $request->validate([
                'profile_picture' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            $user = Auth::user();

            // Delete old profile picture if exists
            if ($user->profile_picture) {
                try {
                    Storage::disk('public')->delete($user->profile_picture);
                } catch (\Exception $e) {
                    // Ignore if file doesn't exist
                    \Log::warning('Could not delete old profile picture: ' . $e->getMessage());
                }
            }

            // Store new profile picture
            $path = $request->file('profile_picture')->store('profile_pictures', 'public');
            
            if (!$path) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to store profile picture.'
                ], 500);
            }
            
            $user->update([
                'profile_picture' => $path
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Profile picture uploaded successfully!',
                'profile_picture' => asset('storage/' . $path)
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Profile picture upload error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while uploading the profile picture: ' . $e->getMessage()
            ], 500);
        }
    }
}
