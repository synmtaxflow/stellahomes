<?php

namespace App\Http\Controllers;

use App\Models\RentSchedule;
use App\Models\Bed;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class RentScheduleController extends Controller
{
    public function index()
    {
        $schedules = RentSchedule::latest()->get();
        
        return view('rent-schedules.index', compact('schedules'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'schedule_type' => 'required|in:begin_of_semester,first_payment,custom',
            'semester_month' => 'nullable|required_if:schedule_type,begin_of_semester|integer|min:1|max:12',
            'custom_start_date' => 'nullable|required_if:schedule_type,custom|date',
            'semester_months' => 'nullable|integer|min:1',
            'delay_days' => 'nullable|integer|min:0|max:365',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed. Please check your input.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Build semester_start_date from month only (for all years)
            // Store with current year for database, but logic will use month only
            $semesterStartDate = null;
            if ($request->schedule_type === 'begin_of_semester' && $request->semester_month) {
                // Create date from month with current year (first day of the month)
                // The month will be used for all years in calculations
                $currentYear = date('Y');
                $semesterStartDate = sprintf('%04d-%02d-01', $currentYear, $request->semester_month);
            }

            // Deactivate all existing active schedules (only one active schedule for entire hostel)
            RentSchedule::where('is_active', true)->update(['is_active' => false]);

            $schedule = RentSchedule::create([
                'schedule_type' => $request->schedule_type,
                'semester_start_date' => $semesterStartDate,
                'custom_start_date' => $request->custom_start_date,
                'semester_months' => $request->semester_months,
                'delay_days' => $request->delay_days ?? 15, // Default to 15 days if not provided
                'notes' => $request->notes,
                'is_active' => true,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Rent schedule recorded successfully!',
                'schedule' => $schedule,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating rent schedule: ' . $e->getMessage(), [
                'request' => $request->all(),
                'exception' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show(RentSchedule $rentSchedule)
    {
        return response()->json($rentSchedule);
    }

    public function update(Request $request, RentSchedule $rentSchedule)
    {
        $validator = Validator::make($request->all(), [
            'schedule_type' => 'required|in:begin_of_semester,first_payment,custom',
            'semester_month' => 'nullable|required_if:schedule_type,begin_of_semester|integer|min:1|max:12',
            'custom_start_date' => 'nullable|required_if:schedule_type,custom|date',
            'semester_months' => 'nullable|integer|min:1',
            'delay_days' => 'nullable|integer|min:0|max:365',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed. Please check your input.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Build semester_start_date from month only (for all years)
            $semesterStartDate = $rentSchedule->semester_start_date;
            if ($request->schedule_type === 'begin_of_semester' && $request->semester_month) {
                // Create date from month with current year (first day of the month)
                // The month will be used for all years in calculations
                $currentYear = date('Y');
                $semesterStartDate = sprintf('%04d-%02d-01', $currentYear, $request->semester_month);
            } elseif ($request->schedule_type !== 'begin_of_semester') {
                $semesterStartDate = null;
            }

            // If activating this schedule, deactivate all others
            $isActive = $request->has('is_active') ? (bool)$request->is_active : $rentSchedule->is_active;
            if ($isActive) {
                RentSchedule::where('id', '!=', $rentSchedule->id)
                    ->where('is_active', true)
                    ->update(['is_active' => false]);
            }

            $rentSchedule->update([
                'schedule_type' => $request->schedule_type,
                'semester_start_date' => $semesterStartDate,
                'custom_start_date' => $request->custom_start_date,
                'semester_months' => $request->semester_months,
                'delay_days' => $request->has('delay_days') ? $request->delay_days : $rentSchedule->delay_days,
                'notes' => $request->notes,
                'is_active' => $isActive,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Rent schedule updated successfully!',
                'schedule' => $rentSchedule,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating rent schedule: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy(RentSchedule $rentSchedule)
    {
        try {
            $rentSchedule->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Rent schedule deleted successfully!',
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting rent schedule: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get active rent schedule for the entire hostel
     */
    public function getSchedule()
    {
        try {
            $schedule = RentSchedule::where('is_active', true)->first();

            return response()->json([
                'success' => true,
                'schedule' => $schedule,
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching rent schedule: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage()
            ], 500);
        }
    }
}
