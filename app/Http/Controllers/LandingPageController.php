<?php

namespace App\Http\Controllers;

use App\Models\Block;
use App\Models\Room;
use App\Models\Bed;
use App\Models\Student;
use App\Models\LandingPageSetting;
use App\Models\Contact;
use App\Models\HostelDetail;
use App\Models\User;
use App\Models\StudentControlNumber;
use App\Services\SmsService;
use App\Models\OwnerDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class LandingPageController extends Controller
{
    /**
     * Display the landing page
     */
    public function index()
    {
        // Get available rooms and beds for display
        $blocks = Block::with(['rooms.beds'])->get();
        
        // Calculate statistics
        $totalRooms = Room::count();
        $totalBeds = Bed::count();
        $availableBeds = Bed::where('status', 'free')->count();
        $availableRooms = 0;
        
        // Count available rooms (rooms without beds that are empty, or rooms with beds that have free beds)
        foreach ($blocks as $block) {
            foreach ($block->rooms as $room) {
                if ($room->has_beds) {
                    $freeBeds = $room->beds->where('status', 'free')->count();
                    if ($freeBeds > 0) {
                        $availableRooms++;
                    }
                } else {
                    $hasStudent = Student::where('room_id', $room->id)
                        ->where('status', 'active')
                        ->whereNull('check_out_date')
                        ->exists();
                    if (!$hasStudent) {
                        $availableRooms++;
                    }
                }
            }
        }
        
        // Get landing page settings (images, colors) from database
        $rawSettings = LandingPageSetting::all()->pluck('value', 'key')->toArray();
        
        // Default values for images and colors
        $defaults = [
            'hero_image' => asset('landing pages/img/hero.jpg'),
            'about_image' => asset('landing pages/img/about.jpg'),
            'primary_color' => '#1e3c72',
            'secondary_color' => '#2a5298',
            'light_color' => '#EFF5F9',
            'dark_color' => '#1D2A4D',
            'hostel_name' => 'ISACK HOSTEL',
        ];
        
        // Process images and colors settings
        $settings = [];
        foreach ($defaults as $key => $defaultValue) {
            // For images stored in storage, prepend storage path
            if ($key === 'hero_image' || $key === 'about_image') {
                if (isset($rawSettings[$key]) && !empty($rawSettings[$key])) {
                    $settings[$key] = asset('storage/' . $rawSettings[$key]);
                } else {
                    $settings[$key] = $defaultValue;
                }
            } else {
                // For colors and text fields, use database value if exists, otherwise use default
                if (isset($rawSettings[$key]) && !empty($rawSettings[$key])) {
                    $settings[$key] = $rawSettings[$key];
                } else {
                    $settings[$key] = $defaultValue;
                }
            }
        }
        
        // Get hostel details
        $hostelDetail = HostelDetail::getHostelDetail();
        $settings['hostel_name'] = $hostelDetail->hostel_name ?? 'ISACK HOSTEL';
        
        // Get contact information from contacts table (single record, always updated)
        $contact = Contact::getContact();
        $settings['contact_address'] = $contact->contact_address ?: '123 Hostel Street';
        $settings['contact_city'] = $contact->contact_city ?: 'Dar es Salaam, Tanzania';
        $settings['contact_phone1'] = $contact->contact_phone1 ?: '+255 XXX XXX XXX';
        $settings['contact_phone2'] = $contact->contact_phone2 ?: '+255 XXX XXX XXX';
        $settings['contact_email1'] = $contact->contact_email1 ?: 'info@isackhostel.com';
        $settings['contact_email2'] = $contact->contact_email2 ?: 'bookings@isackhostel.com';
        $settings['whatsapp_number'] = $contact->whatsapp_number ?: '+255 XXX XXX XXX';
        $settings['booking_timeout_hours'] = $contact->booking_timeout_hours ?: 24;
        
        // Get pending bookings with time remaining for display on landing page
        $pendingBookings = Student::with(['room', 'bed'])
            ->where('status', 'booked')
            ->orderBy('created_at', 'desc')
            ->limit(10) // Show last 10 pending bookings
            ->get();

        // Get booking timeout from contacts table
        $timeoutHours = $settings['booking_timeout_hours'] ?? 24;

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

        // Get owner account details for payment display
        $owner = User::where('role', 'owner')->first();
        $ownerDetail = $owner ? $owner->ownerDetail : null;

        // Log for debugging
        \Log::info('Landing page settings loaded', [
            'contact_address' => $settings['contact_address'],
            'contact_phone1' => $settings['contact_phone1'],
            'booking_timeout_hours' => $settings['booking_timeout_hours'],
        ]);
        
        return view('landing.index', compact('blocks', 'totalRooms', 'totalBeds', 'availableBeds', 'availableRooms', 'settings', 'bookingsWithTime', 'ownerDetail'));
    }

    /**
     * Handle booking form submission
     */
    public function book(Request $request)
    {
        // Normalize request data - convert empty strings to null for bed_id
        if ($request->has('bed_id') && $request->bed_id === '') {
            $request->merge(['bed_id' => null]);
        }
        
        // Check if phone exists and determine if booking is allowed
        $existingStudent = Student::where('phone', $request->phone)->first();
        $allowReBooking = false;
        
        if ($existingStudent) {
            // Allow re-booking if student is removed or terminated
            if (in_array($existingStudent->status, ['removed', 'terminated'])) {
                $allowReBooking = true;
            }
            // Allow re-booking if status is 'booked' and booking has expired
            elseif ($existingStudent->status === 'booked') {
                $bed = $existingStudent->bed;
                if ($bed && $bed->booking_expires_at) {
                    $expiresAt = Carbon::parse($bed->booking_expires_at);
                    if ($expiresAt->isPast()) {
                        $allowReBooking = true;
                    }
                } else {
                    // For rooms without beds, check created_at + timeout
                    $contact = Contact::getContact();
                    $timeoutHours = $contact->booking_timeout_hours ?? 24;
                    $createdAt = Carbon::parse($existingStudent->created_at);
                    $expiresAt = $createdAt->copy()->addHours($timeoutHours);
                    if ($expiresAt->isPast()) {
                        $allowReBooking = true;
                    }
                }
            }
        }
        
        // Validate phone number format: 255 + (6 or 7) + 8 more digits = 12 digits total
        // Simplified form - only name, phone, and check-in date
        // Exclude removed/terminated students and expired bookings from unique check
        $validator = Validator::make($request->all(), [
            'block_id' => 'required|integer|exists:blocks,id',
            'room_id' => 'required|integer|exists:rooms,id',
            'bed_id' => 'nullable|integer|exists:beds,id',
            'full_name' => 'required|string|max:255',
            'phone' => [
                'required',
                'string',
                'regex:/^255[67]\d{8}$/',
            ],
            'check_in_date' => 'required|date|after_or_equal:today',
            'accept_terms' => 'required|accepted',
        ], [
            'block_id.required' => 'Please select a block.',
            'block_id.integer' => 'Block ID must be a valid number.',
            'block_id.exists' => 'The selected block is invalid or does not exist.',
            'room_id.required' => 'Please select a room.',
            'room_id.integer' => 'Room ID must be a valid number.',
            'room_id.exists' => 'The selected room is invalid or does not exist.',
            'bed_id.integer' => 'Bed ID must be a valid number.',
            'bed_id.exists' => 'The selected bed is invalid or does not exist.',
            'full_name.required' => 'Full name is required.',
            'full_name.string' => 'Full name must be text.',
            'full_name.max' => 'Full name cannot exceed 255 characters.',
            'phone.required' => 'Phone number is required.',
            'phone.string' => 'Phone number must be text.',
            'phone.regex' => 'Phone number must start with 255 followed by 6 or 7, then 8 more digits (e.g., 255612345678 or 255712345678).',
            'phone.unique' => 'This phone number is already registered with an active booking. Please use a different number.',
            'check_in_date.required' => 'Check-in date is required.',
            'check_in_date.date' => 'Please enter a valid date.',
            'check_in_date.after_or_equal' => 'Check-in date must be today or a future date.',
            'accept_terms.required' => 'You must accept the Terms and Conditions to proceed.',
            'accept_terms.accepted' => 'You must accept the Terms and Conditions to proceed.',
        ]);

        // Custom validation: Check if phone exists and re-booking is allowed
        if ($existingStudent && !$allowReBooking) {
            if ($existingStudent->status === 'booked') {
                $validator->errors()->add('phone', 'This phone number has a pending booking. Please wait for it to expire or complete the payment.');
            } else {
                $validator->errors()->add('phone', 'This phone number is already registered with an active booking. Please use a different number.');
            }
        }

        if ($validator->fails()) {
            // Log validation errors for debugging
            \Log::error('Booking validation failed', [
                'errors' => $validator->errors()->all(),
                'request_data' => $request->except(['_token']),
            ]);
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed. Please check the errors below.',
                    'errors' => $validator->errors()
                ], 422);
            }
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Please correct the errors and try again.');
        }

        DB::beginTransaction();
        try {
            // If phone exists with removed/terminated status or expired booking, delete the old record first
            // This must be done INSIDE the transaction to avoid duplicate entry errors
            if ($existingStudent && $allowReBooking) {
                // If booking expired, free up the bed first
                if ($existingStudent->status === 'booked') {
                    $oldBed = $existingStudent->bed;
                    if ($oldBed) {
                        $oldBed->update([
                            'status' => 'free',
                            'booking_expires_at' => null,
                        ]);
                    }
                }
                
                // Delete user account if exists
                if ($existingStudent->user_id) {
                    $oldUser = User::find($existingStudent->user_id);
                    if ($oldUser) {
                        $oldUser->delete();
                    }
                }
                // Delete old student record BEFORE creating new one
                $existingStudent->delete();
            }
            $room = Room::findOrFail($request->room_id);
            
            // Check if room has beds
            if ($room->has_beds) {
                if (!$request->bed_id) {
                    if ($request->ajax()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Please select a bed to continue.',
                            'errors' => ['bed_id' => ['Please select a bed.']]
                        ], 422);
                    }
                    return redirect()->back()
                        ->withErrors(['bed_id' => 'Please select a bed.'])
                        ->withInput()
                        ->with('error', 'Please select a bed to continue.');
                }

                $bed = Bed::findOrFail($request->bed_id);
                
                // Check if bed is free
                if ($bed->status !== 'free') {
                    if ($request->ajax()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'This bed is not available. Please select another bed.',
                            'errors' => ['bed_id' => ['This bed is not available.']]
                        ], 422);
                    }
                    return redirect()->back()
                        ->withErrors(['bed_id' => 'This bed is not available.'])
                        ->withInput()
                        ->with('error', 'This bed is not available. Please select another bed.');
                }

                // Check if bed belongs to the selected room
                if ($bed->room_id != $request->room_id) {
                    if ($request->ajax()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Invalid bed selection.',
                            'errors' => ['bed_id' => ['Invalid bed selection.']]
                        ], 422);
                    }
                    return redirect()->back()
                        ->withErrors(['bed_id' => 'Invalid bed selection.'])
                        ->withInput()
                        ->with('error', 'Invalid bed selection.');
                }
            } else {
                // Check if room is already occupied
                $occupiedCount = Student::where('room_id', $request->room_id)
                    ->where('status', 'active')
                    ->whereNull('check_out_date')
                    ->count();
                
                if ($occupiedCount > 0) {
                    if ($request->ajax()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'This room is already occupied.',
                            'errors' => ['room_id' => ['This room is already occupied.']]
                        ], 422);
                    }
                    return redirect()->back()
                        ->withErrors(['room_id' => 'This room is already occupied.'])
                        ->withInput()
                        ->with('error', 'This room is already occupied.');
                }
            }

            // Get booking timeout from contacts table (flexible: minutes or hours)
            $contact = Contact::getContact();
            $timeoutUnit = $contact->booking_timeout_unit ?? 'hours';
            $timeoutValue = $contact->booking_timeout_value ?? ($contact->booking_timeout_hours ?? 24);
            
            // Calculate expiration based on unit
            if ($timeoutUnit === 'minutes') {
                $timeoutValue = (int) $timeoutValue;
                if ($timeoutValue < 1 || $timeoutValue > 10080) { // Max 7 days in minutes
                    $timeoutValue = 1440; // Default to 24 hours (1440 minutes)
                }
                $expiresAt = Carbon::now()->addMinutes($timeoutValue);
                $timeoutDisplay = $timeoutValue . ' dakika';
            } else {
                $timeoutValue = (int) $timeoutValue;
                if ($timeoutValue < 1 || $timeoutValue > 168) { // Max 7 days
                    $timeoutValue = 24; // Default to 24 hours
                }
                $expiresAt = Carbon::now()->addHours($timeoutValue);
                $timeoutDisplay = $timeoutValue . ' masaa';
            }
            
            \Log::info('Booking timeout used', [
                'timeout_unit' => $timeoutUnit,
                'timeout_value' => $timeoutValue,
                'expires_at' => $expiresAt->toDateTimeString(),
            ]);

            // Generate student number and email from phone number
            $studentNumber = 'STU-' . substr($request->phone, -6); // Last 6 digits of phone
            $email = 'student' . substr($request->phone, -6) . '@isackhostel.com';

            // Create student record with booked status
            $student = Student::create([
                'student_number' => $studentNumber,
                'full_name' => $request->full_name,
                'email' => $email,
                'phone' => $request->phone,
                'room_id' => $request->room_id,
                'bed_id' => $room->has_beds ? $request->bed_id : null,
                'check_in_date' => $request->check_in_date,
                'status' => 'booked',
            ]);

            // Update bed status to pending_payment and set expiration
            if ($room->has_beds && $request->bed_id) {
                $bed->update([
                    'status' => 'pending_payment',
                    'booking_expires_at' => $expiresAt,
                ]);
            }

            // Generate username (phone number) and password (last name)
            $nameParts = explode(' ', trim($request->full_name));
            $lastName = end($nameParts);
            $username = preg_replace('/[^0-9]/', '', $request->phone);
            $password = $lastName;

            // Create user account for student
            $user = User::create([
                'name' => $request->full_name,
                'username' => $username,
                'email' => $email, // Use generated email
                'password' => Hash::make($password),
                'role' => 'student',
            ]);

            // Generate unique control number for this booking
            $smsService = new SmsService();
            $controlNumber = $smsService->generateControlNumber();
            
            // Create control number record with expiration
            $controlNumberRecord = StudentControlNumber::create([
                'student_id' => $student->id,
                'control_number' => $controlNumber,
                'starting_balance' => 100000,
                'bill_amount' => 0,
                'total_paid' => 0,
                'remaining_balance' => 100000,
                'is_active' => true,
                'is_fully_paid' => false,
                'expires_at' => $expiresAt,
                'is_expired' => false,
            ]);
            
            // Get owner details for SMS notification
            $owner = User::where('role', 'owner')->first();
            $ownerDetail = $owner ? $owner->ownerDetail : null;
            
            // Get hostel name from hostel details
            $hostelDetail = HostelDetail::getHostelDetail();
            $hostelName = $hostelDetail->hostel_name ?? 'ISACK HOSTEL';

            // Format expiration date for SMS
            $expiresAtFormatted = $expiresAt->format('d/m/Y H:i');
            
            // Send SMS to student with control number and payment instructions
            $studentMessage = "Welcome to {$hostelName}!\n";
            $studentMessage .= "Your booking has been confirmed.\n";
            $studentMessage .= "Control Number: {$controlNumber}\n";
            $studentMessage .= "Lipa kupitia Control Number: {$controlNumber}\n";
            $studentMessage .= "Username: {$username}\n";
            $studentMessage .= "Password: {$password}\n";
            $studentMessage .= "Please make payment within {$timeoutDisplay} (Expires: {$expiresAtFormatted}) to secure your booking.\n";
            $studentMessage .= "Thank you!";
            $smsService->sendSms($request->phone, $studentMessage, $controlNumber);

            // Send SMS to owner with control number
            if ($owner && $ownerDetail && $ownerDetail->phone_number) {
                $ownerMessage = "New Booking! Control No: {$controlNumber}. Student: {$request->full_name}, Number: {$studentNumber}, Phone: {$request->phone}, Room: {$room->name}";
                if ($room->has_beds && $bed) {
                    $ownerMessage .= ", Bed: {$bed->name}";
                }
                $ownerControlNumber = $smsService->generateControlNumber();
                $smsService->sendSms($ownerDetail->phone_number, $ownerMessage, $ownerControlNumber);
            }

            // Update student with user_id (if column exists)
            if (Schema::hasColumn('students', 'user_id')) {
                $student->update(['user_id' => $user->id]);
            }

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Booking successful! Your login credentials and control number have been sent to your phone via SMS.',
                    'booking_info' => [
                        'username' => $username,
                        'password' => $password,
                        'control_number' => $controlNumber,
                        'room' => $room->name,
                        'bed' => $room->has_beds && $bed ? $bed->name : null,
                        'expires_at' => $expiresAt->format('d/m/Y H:i'),
                        'expires_at_formatted' => $expiresAt->format('F d, Y \a\t H:i'),
                        'timeout_display' => $timeoutDisplay,
                    ]
                ], 200);
            }

            return redirect()->back()
                ->with('success', 'Booking successful! Your login credentials have been sent to your phone.')
                ->with('booking_info', [
                    'username' => $username,
                    'password' => $password,
                ]);

        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            
            // Check for specific database errors
            $errorMessage = $e->getMessage();
            $userMessage = 'Unable to complete booking. Please try again or contact support if the problem persists.';
            
            // Check for duplicate entry errors
            if (str_contains($errorMessage, 'Duplicate entry')) {
                if (str_contains($errorMessage, 'users_email_unique') || str_contains($errorMessage, 'users_username_unique')) {
                    $userMessage = 'This phone number is already registered. Please use a different phone number or login if you already have an account.';
                } elseif (str_contains($errorMessage, 'students_phone_unique')) {
                    $userMessage = 'This phone number is already registered with an active booking. Please use a different phone number.';
                } else {
                    $userMessage = 'This information is already registered. Please use different details or contact support.';
                }
            } elseif (str_contains($errorMessage, 'Integrity constraint violation')) {
                $userMessage = 'Unable to complete booking. The selected room or bed may no longer be available. Please try selecting a different option.';
            }
            
            \Log::error('Booking database error: ' . $errorMessage, [
                'error_code' => $e->getCode(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->except(['_token', 'password']),
            ]);
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $userMessage
                ], 422);
            }
            
            return redirect()->back()
                ->withInput()
                ->with('error', $userMessage);
        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('Booking error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->except(['_token', 'password']),
            ]);
            
            $userMessage = 'An unexpected error occurred. Please try again or contact support if the problem persists.';
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $userMessage
                ], 500);
            }
            
            return redirect()->back()
                ->withInput()
                ->with('error', $userMessage);
        }
    }

    /**
     * Get rooms by block (Public API for landing page)
     */
    public function getRoomsByBlock($blockId)
    {
        try {
            $block = Block::with(['rooms.beds', 'rooms.students'])->findOrFail($blockId);

            $rooms = $block->rooms->map(function($room) {
                $allBeds = $room->beds;
                $totalBeds = $allBeds->count();
                
                // Free expired bookings
                $freeBeds = $allBeds->filter(function($bed) {
                    if ($bed->status === 'free') {
                        return true;
                    }
                    if ($bed->status === 'pending_payment' && $bed->booking_expires_at && Carbon::parse($bed->booking_expires_at)->isPast()) {
                        return true;
                    }
                    return false;
                })->count();
                
                // Check if room without beds is occupied
                $isOccupied = false;
                if (!$room->has_beds) {
                    $isOccupied = Student::where('room_id', $room->id)
                        ->where('status', 'active')
                        ->whereNull('check_out_date')
                        ->exists();
                }
                
                return [
                    'id' => $room->id,
                    'name' => $room->name,
                    'location' => $room->location,
                    'has_beds' => $room->has_beds,
                    'total_beds' => $totalBeds,
                    'free_beds' => $freeBeds,
                    'is_occupied' => $isOccupied,
                ];
            });

            return response()->json([
                'success' => true,
                'rooms' => $rooms
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching rooms: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get beds by room (Public API for landing page)
     */
    public function getBedsByRoom($roomId)
    {
        try {
            $room = Room::with('beds')->findOrFail($roomId);
            
            // Free expired bookings
            Bed::where('status', 'pending_payment')
                ->where('booking_expires_at', '<', Carbon::now())
                ->update([
                    'status' => 'free',
                    'booking_expires_at' => null,
                ]);
            
            $freeBeds = $room->beds->where('status', 'free')->map(function($bed) {
                return [
                    'id' => $bed->id,
                    'name' => $bed->name,
                    'rent_price' => $bed->rent_price,
                    'rent_duration' => $bed->rent_duration,
                ];
            })->values();

            return response()->json([
                'success' => true,
                'beds' => $freeBeds,
                'has_free_beds' => $freeBeds->count() > 0
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching beds: ' . $e->getMessage()
            ], 500);
        }
    }
}

