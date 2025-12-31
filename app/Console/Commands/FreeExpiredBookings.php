<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Bed;
use App\Models\Student;
use App\Models\User;
use App\Models\StudentControlNumber;
use App\Services\SmsService;
use Carbon\Carbon;

class FreeExpiredBookings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bookings:free-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Free beds that have expired bookings (not paid within timeout period)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for expired bookings...');
        
        $count = 0;
        $smsService = new SmsService();
        
        // Check expired control numbers first (new method)
        $expiredControlNumbers = StudentControlNumber::where('is_active', true)
            ->where('is_expired', false)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', Carbon::now())
            ->get();
        
        foreach ($expiredControlNumbers as $controlNumber) {
            // Mark control number as expired
            $controlNumber->update([
                'is_expired' => true,
                'is_active' => false,
            ]);
            
            // Find student associated with this control number
            $student = Student::find($controlNumber->student_id);
            
            if ($student && $student->status === 'booked') {
                // Free the bed if student has one
                if ($student->bed_id) {
                    $bed = Bed::find($student->bed_id);
                    if ($bed) {
                        $bed->update([
                            'status' => 'free',
                            'booking_expires_at' => null,
                        ]);
                    }
                }
                
                // Send alert SMS to student
                $hostelDetail = \App\Models\HostelDetail::getHostelDetail();
                $hostelName = $hostelDetail->hostel_name ?? 'ISACK HOSTEL';
                $message = "Hello {$student->full_name}. Your booking at {$hostelName} has expired. Please make a new booking. Thank you!";
                $smsService->sendSms($student->phone, $message);
                
                // Delete user account - try multiple methods to find the user
                $user = null;
                
                // Method 1: Try to find by user_id if column exists
                if (isset($student->user_id) && $student->user_id) {
                    $user = User::find($student->user_id);
                }
                
                // Method 2: If not found, try to find by email (generated email format)
                if (!$user && $student->email) {
                    $user = User::where('email', $student->email)->first();
                }
                
                // Method 3: If still not found, try to find by username (phone number)
                if (!$user && $student->phone) {
                    $username = preg_replace('/[^0-9]/', '', $student->phone);
                    $user = User::where('username', $username)
                        ->where('role', 'student')
                        ->first();
                }
                
                // Delete the user if found
                if ($user) {
                    $user->delete();
                }
                
                // Delete student record completely
                $student->delete();
                $count++;
            }
        }
        
        // Also check expired beds by booking_expires_at (old method for backward compatibility)
        $expiredBeds = Bed::where('status', 'pending_payment')
            ->where('booking_expires_at', '<', Carbon::now())
            ->get();
        
        foreach ($expiredBeds as $bed) {
            // Find student with booked status for this bed
            $student = Student::where('bed_id', $bed->id)
                ->where('status', 'booked')
                ->first();
            
            if ($student) {
                // Send alert SMS to student
                $hostelDetail = \App\Models\HostelDetail::getHostelDetail();
                $hostelName = $hostelDetail->hostel_name ?? 'ISACK HOSTEL';
                $message = "Hello {$student->full_name}. Your booking at {$hostelName} has expired. Please make a new booking. Thank you!";
                $smsService->sendSms($student->phone, $message);
                
                // Delete user account - try multiple methods to find the user
                $user = null;
                
                // Method 1: Try to find by user_id if column exists
                if (isset($student->user_id) && $student->user_id) {
                    $user = User::find($student->user_id);
                }
                
                // Method 2: If not found, try to find by email (generated email format)
                if (!$user && $student->email) {
                    $user = User::where('email', $student->email)->first();
                }
                
                // Method 3: If still not found, try to find by username (phone number)
                if (!$user && $student->phone) {
                    $username = preg_replace('/[^0-9]/', '', $student->phone);
                    $user = User::where('username', $username)
                        ->where('role', 'student')
                        ->first();
                }
                
                // Delete the user if found
                if ($user) {
                    $user->delete();
                }
                
                // Delete student record completely
                $student->delete();
            }
            
            // Free the bed
            $bed->update([
                'status' => 'free',
                'booking_expires_at' => null,
            ]);
            
            $count++;
        }
        
        // Also handle expired bookings for rooms without beds (key rooms)
        $contact = \App\Models\Contact::getContact();
        $timeoutHours = $contact->booking_timeout_hours ?? 24;
        
        $expiredStudents = Student::where('status', 'booked')
            ->whereNull('bed_id')
            ->where('created_at', '<', Carbon::now()->subHours($timeoutHours))
            ->get();
        
        foreach ($expiredStudents as $student) {
            // Send alert SMS
            $hostelDetail = \App\Models\HostelDetail::getHostelDetail();
            $hostelName = $hostelDetail->hostel_name ?? 'ISACK HOSTEL';
            $message = "Hello {$student->full_name}. Your booking at {$hostelName} has expired. Please make a new booking. Thank you!";
            $smsService->sendSms($student->phone, $message);
            
            // Delete user account - try multiple methods to find the user
            $user = null;
            
            // Method 1: Try to find by user_id if column exists
            if (isset($student->user_id) && $student->user_id) {
                $user = User::find($student->user_id);
            }
            
            // Method 2: If not found, try to find by email (generated email format)
            if (!$user && $student->email) {
                $user = User::where('email', $student->email)->first();
            }
            
            // Method 3: If still not found, try to find by username (phone number)
            if (!$user && $student->phone) {
                $username = preg_replace('/[^0-9]/', '', $student->phone);
                $user = User::where('username', $username)
                    ->where('role', 'student')
                    ->first();
            }
            
            // Delete the user if found
            if ($user) {
                $user->delete();
            }
            
            // Delete student record completely
            $student->delete();
            $count++;
        }
        
        $this->info("Freed {$count} expired booking(s).");
        
        return 0;
    }
}
