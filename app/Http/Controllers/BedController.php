<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Bed;

class BedController extends Controller
{
    /**
     * Display the specified bed
     */
    public function show(Bed $bed)
    {
        return response()->json($bed->load(['room.block']));
    }

    /**
     * Update the specified bed
     */
    public function update(Request $request, Bed $bed)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'rent_price' => 'nullable|numeric|min:0',
            'rent_duration' => 'nullable|in:monthly,semester',
            'semester_months' => 'nullable|integer|min:1|required_if:rent_duration,semester',
            'payment_frequency' => 'nullable|in:one_month,two_months,three_months,four_months,five_months,six_months|required_if:rent_duration,monthly',
            'status' => 'required|in:free,occupied,pending_payment',
        ], [
            'payment_frequency.required_if' => 'Payment frequency is required when duration is monthly.',
            'semester_months.required_if' => 'Semester months is required when duration is semester.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $bed->name = $request->name;
        $bed->rent_price = $request->rent_price ?? null;
        $bed->rent_duration = $request->rent_duration ?? null;
        $bed->semester_months = ($request->rent_duration === 'semester') ? ($request->semester_months ?? null) : null;
        $bed->payment_frequency = ($request->rent_duration === 'monthly') ? ($request->payment_frequency ?? null) : null;
        $bed->status = $request->status;
        $bed->save();

        return response()->json([
            'success' => true,
            'message' => 'Bed updated successfully!',
            'bed' => $bed->load(['room.block'])
        ]);
    }
}

