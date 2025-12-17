<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Block;
use App\Models\Room;
use App\Models\Bed;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StudentController extends Controller
{
    public function index()
    {
        $students = Student::with(['room.block', 'bed'])->latest()->get();
        $blocks = \App\Models\Block::with(['rooms.beds', 'rooms.students'])->get();
        return view('students.index', compact('students', 'blocks'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'student_number' => 'required|string|max:255|unique:students,student_number',
            'full_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => [
                'nullable',
                'string',
                'regex:/^255[67]\d{8}$/',
                'unique:students,phone',
            ],
            'national_id' => 'nullable|string|max:255',
            'course' => 'nullable|string|max:255',
            'year_of_study' => 'nullable|string|max:255',
            'room_id' => 'required|exists:rooms,id',
            'bed_id' => 'nullable|exists:beds,id',
            'check_in_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ], [
            'phone.regex' => 'Phone number must start with 255 followed by 6 or 7, then 8 more digits (e.g., 255612345678 or 255712345678).',
            'phone.unique' => 'This phone number is already registered. Please use a different number.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed. Please check your input.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $room = Room::findOrFail($request->room_id);
            
            // If room has beds, bed_id is required
            if ($room->has_beds && !$request->bed_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please select a bed for this room.',
                    'errors' => ['bed_id' => ['Bed selection is required for rooms with beds.']]
                ], 422);
            }

            // If room has beds, check if bed is available
            if ($room->has_beds && $request->bed_id) {
                $bed = Bed::findOrFail($request->bed_id);
                if ($bed->status !== 'free') {
                    return response()->json([
                        'success' => false,
                        'message' => 'Selected bed is not available. Please select a free bed.',
                        'errors' => ['bed_id' => ['The selected bed is already occupied or has pending payment.']]
                    ], 422);
                }

                // Check if bed belongs to the selected room
                if ($bed->room_id != $request->room_id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Selected bed does not belong to the selected room.',
                        'errors' => ['bed_id' => ['Invalid bed selection.']]
                    ], 422);
                }
            }

            // If room doesn't have beds, check if room is available
            if (!$room->has_beds) {
                $occupiedCount = Student::where('room_id', $request->room_id)
                    ->where('status', 'active')
                    ->whereNull('check_out_date')
                    ->count();
                
                // For now, we allow one student per room without beds
                // You can modify this logic based on your requirements
                if ($occupiedCount > 0) {
                    return response()->json([
                        'success' => false,
                        'message' => 'This room is already occupied.',
                        'errors' => ['room_id' => ['The selected room is already occupied.']]
                    ], 422);
                }
            }

            $student = Student::create([
                'student_number' => $request->student_number,
                'full_name' => $request->full_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'national_id' => $request->national_id,
                'course' => $request->course,
                'year_of_study' => $request->year_of_study,
                'room_id' => $request->room_id,
                'bed_id' => $room->has_beds ? $request->bed_id : null,
                'check_in_date' => $request->check_in_date ?? now(),
                'status' => 'active',
                'notes' => $request->notes,
            ]);

            // Update bed status if room has beds - set to pending_payment (booked) when student is added
            if ($room->has_beds && $request->bed_id) {
                $bed->update(['status' => 'pending_payment']);
            }

            return response()->json([
                'success' => true,
                'message' => 'Student registered successfully!',
                'student' => $student->load(['room.block', 'bed'])
            ]);

        } catch (\Exception $e) {
            Log::error('Error creating student: ' . $e->getMessage(), [
                'request' => $request->all(),
                'exception' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getRoomsByBlock($blockId)
    {
        try {
            $block = Block::with(['rooms.beds'])->findOrFail($blockId);

            $rooms = $block->rooms->map(function($room) {
                // Get all beds for this room (not filtered)
                $allBeds = $room->beds;
                $totalBeds = $allBeds->count();
                $freeBeds = $allBeds->where('status', 'free')->count();
                $occupiedBeds = $allBeds->where('status', '!=', 'free')->count();
                
                // Determine room status
                if ($room->has_beds) {
                    if ($freeBeds > 0) {
                        $status = 'available';
                    } elseif ($occupiedBeds > 0 && $freeBeds == 0) {
                        $status = 'occupied';
                    } else {
                        $status = 'empty';
                    }
                } else {
                    $hasStudent = Student::where('room_id', $room->id)
                        ->where('status', 'active')
                        ->whereNull('check_out_date')
                        ->exists();
                    $status = $hasStudent ? 'occupied' : 'available';
                }

                // Get only free beds for selection
                $freeBedsList = $allBeds->where('status', 'free')->map(function($bed) {
                    return [
                        'id' => $bed->id,
                        'name' => $bed->name,
                        'rent_price' => $bed->rent_price,
                        'rent_duration' => $bed->rent_duration,
                    ];
                })->values();

                return [
                    'id' => $room->id,
                    'name' => $room->name,
                    'location' => $room->location,
                    'has_beds' => $room->has_beds,
                    'status' => $status,
                    'total_beds' => $totalBeds,
                    'free_beds' => $freeBeds,
                    'occupied_beds' => $occupiedBeds,
                    'beds' => $freeBedsList
                ];
            });

            return response()->json([
                'success' => true,
                'rooms' => $rooms
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching rooms: ' . $e->getMessage(), [
                'block_id' => $blockId,
                'exception' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error fetching rooms: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show(Student $student)
    {
        return response()->json($student->load(['room.block', 'bed']));
    }

    public function update(Request $request, Student $student)
    {
        $validator = Validator::make($request->all(), [
            'student_number' => 'required|string|max:255|unique:students,student_number,' . $student->id,
            'full_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:255',
            'national_id' => 'nullable|string|max:255',
            'course' => 'nullable|string|max:255',
            'year_of_study' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive,graduated,removed',
            'check_in_date' => 'nullable|date',
            'check_out_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $student->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Student updated successfully!',
            'student' => $student->load(['room.block', 'bed'])
        ]);
    }

    public function destroy(Student $student)
    {
        // Free up the bed if student has one
        if ($student->bed_id) {
            $bed = Bed::find($student->bed_id);
            if ($bed) {
                $bed->update(['status' => 'free']);
            }
        }

        $student->delete();

        return response()->json([
            'success' => true,
            'message' => 'Student deleted successfully!'
        ]);
    }

    /**
     * Remove student (change status to removed and free bed)
     * Only allowed if rent has expired
     */
    public function remove(Student $student)
    {
        try {
            // Check if student has active rent
            $lastPayment = Payment::where('student_id', $student->id)
                ->where('status', 'completed')
                ->whereNotNull('period_end_date')
                ->orderBy('period_end_date', 'desc')
                ->first();

            $today = Carbon::today();
            $rentExpired = false;

            if ($lastPayment && $lastPayment->period_end_date) {
                $rentEndDate = Carbon::parse($lastPayment->period_end_date);
                $rentExpired = $rentEndDate->isPast();
            } else {
                // No payment recorded - consider as expired (can be removed)
                $rentExpired = true;
            }

            if (!$rentExpired) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hauwezi kumwondoa mwanafunzi huyu kwa sababu rent yake bado haijaisha.'
                ], 422);
            }

            DB::beginTransaction();
            try {
                // Update student status to removed
                $student->update([
                    'status' => 'removed',
                    'check_out_date' => now(),
                ]);

                // Free up the bed if student has one
                if ($student->bed_id) {
                    $bed = Bed::find($student->bed_id);
                    if ($bed) {
                        $bed->update(['status' => 'free']);
                    }
                }

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Mwanafunzi ameondolewa kwa mafanikio. Kitanda chake sasa kiko huru.'
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Error removing student: ' . $e->getMessage(), [
                    'student_id' => $student->id,
                    'exception' => $e->getTraceAsString()
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Kuna hitilafu wakati wa kuondoa mwanafunzi. Tafadhali jaribu tena.'
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('Error in remove student: ' . $e->getMessage(), [
                'student_id' => $student->id,
                'exception' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Kuna hitilafu. Tafadhali jaribu tena.'
            ], 500);
        }
    }
}
