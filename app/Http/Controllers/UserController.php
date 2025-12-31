<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Block;
use App\Models\Room;
use App\Models\Bed;
use App\Models\Student;
use App\Models\Payment;
use App\Models\Contact;
use Carbon\Carbon;

class UserController extends Controller
{
    /**
     * Store a newly created user (for owner to add matron/student)
     */
    public function store(Request $request)
    {
        // Only owner can add users
        if (Auth::user()->role !== 'owner') {
            return redirect()->back()->with('error', 'Unauthorized action.');
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => 'required|in:matron,patron,student',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        return redirect()->back()->with('success', ucfirst($request->role) . ' added successfully!');
    }

    /**
     * Display a listing of users (for owner)
     */
    public function index()
    {
        if (Auth::user()->role !== 'owner') {
            return redirect()->back()->with('error', 'Unauthorized action.');
        }

        // Get statistics
        $totalBlocks = Block::count();
        $totalStudents = Student::where('status', 'active')->whereNull('check_out_date')->count();
        
        // Empty beds (status = 'free')
        $emptyBeds = Bed::where('status', 'free')->count();
        
        // Empty rooms (rooms without beds that have no active students)
        $roomsWithoutBeds = Room::where('has_beds', false)->get();
        $emptyRooms = 0;
        foreach ($roomsWithoutBeds as $room) {
            $hasActiveStudent = Student::where('room_id', $room->id)
                ->where('status', 'active')
                ->whereNull('check_out_date')
                ->exists();
            if (!$hasActiveStudent) {
                $emptyRooms++;
            }
        }
        
        // Rooms with beds that are completely empty
        $roomsWithBeds = Room::where('has_beds', true)->get();
        foreach ($roomsWithBeds as $room) {
            $freeBedsInRoom = $room->beds()->where('status', 'free')->count();
            $totalBedsInRoom = $room->beds()->count();
            if ($totalBedsInRoom > 0 && $freeBedsInRoom == $totalBedsInRoom) {
                $emptyRooms++;
            }
        }
        
        // Payment statistics for last 6 months
        $paymentData = [];
        $bedOccupancyData = [];
        
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthName = $date->format('M Y');
            
            // Payments for this month
            $monthPayments = Payment::whereYear('payment_date', $date->year)
                ->whereMonth('payment_date', $date->month)
                ->where('status', 'completed')
                ->sum('amount');
            
            $paymentData[] = [
                'month' => $monthName,
                'amount' => $monthPayments
            ];
            
            // Bed occupancy for this month (approximate - using current status)
            if ($i == 0) {
                $totalBeds = Bed::count();
                $occupiedBeds = Bed::whereIn('status', ['occupied', 'pending_payment'])->count();
                $bedOccupancyData[] = [
                    'month' => $monthName,
                    'occupied' => $occupiedBeds,
                    'empty' => $totalBeds - $occupiedBeds
                ];
            }
        }
        
        // Current bed occupancy
        $totalBeds = Bed::count();
        $occupiedBeds = Bed::whereIn('status', ['occupied', 'pending_payment'])->count();
        $freeBeds = Bed::where('status', 'free')->count();

        // Get pending bookings with time remaining
        $pendingBookings = Student::with(['room', 'bed'])
            ->where('status', 'pending_payment')
            ->orderBy('created_at', 'desc')
            ->get();

        // Get booking timeout from contacts table
        $contact = Contact::getContact();
        $timeoutHours = $contact->booking_timeout_hours ?? 24;

        // Calculate time remaining for each booking
        $bookingsWithTime = $pendingBookings->map(function($booking) use ($timeoutHours) {
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
                'student' => $booking,
                'room' => $booking->room,
                'bed' => $bed,
                'expires_at' => $expiresAt,
                'time_remaining' => $timeRemaining,
                'is_expired' => $isExpired,
                'hours_remaining' => $hoursRemaining,
            ];
        });

        $newBookingsCount = $pendingBookings->count();
        $expiredBookingsCount = $bookingsWithTime->where('is_expired', true)->count();

        return view('dashboard.owner', compact(
            'totalBlocks',
            'totalStudents',
            'emptyBeds',
            'emptyRooms',
            'paymentData',
            'bedOccupancyData',
            'totalBeds',
            'occupiedBeds',
            'freeBeds',
            'bookingsWithTime',
            'newBookingsCount',
            'expiredBookingsCount',
            'timeoutHours'
        ));
    }

    /**
     * Remove the specified user
     */
    public function destroy($id)
    {
        if (Auth::user()->role !== 'owner') {
            return redirect()->back()->with('error', 'Unauthorized action.');
        }

        $user = User::findOrFail($id);
        
        // Prevent deleting owner
        if ($user->role === 'owner') {
            return redirect()->back()->with('error', 'Cannot delete owner account.');
        }

        $user->delete();

        return redirect()->back()->with('success', 'User deleted successfully!');
    }
}

