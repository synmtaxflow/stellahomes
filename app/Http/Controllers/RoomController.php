<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Models\Room;
use App\Models\Block;
use App\Models\Bed;
use App\Models\RoomItem;

class RoomController extends Controller
{
    /**
     * Display a listing of rooms
     */
    public function index()
    {
        $rooms = Room::with(['block', 'beds', 'items'])->orderBy('created_at', 'desc')->get();
        $blocks = Block::orderBy('name')->get();
        return view('rooms.index', compact('rooms', 'blocks'));
    }

    /**
     * Store a newly created room (AJAX)
     */
    public function store(Request $request)
    {
        // Check if multiple rooms are being added
        if ($request->has('rooms') && is_array($request->rooms)) {
            return $this->storeMultipleRooms($request);
        }

        // Single room creation (backward compatibility)
        $validator = Validator::make($request->all(), [
            'block_id' => 'required|exists:blocks,id',
            'name' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'has_beds' => 'boolean',
            'rent_price' => 'nullable|numeric|min:0|required_without:beds',
            'rent_duration' => 'nullable|in:monthly,semester|required_with:rent_price',
            'semester_months' => 'nullable|integer|min:1|required_if:rent_duration,semester',
            'payment_frequency' => 'nullable|in:one_month,two_months,three_months,four_months,five_months,six_months|required_if:rent_duration,monthly',
            'items' => 'nullable|array',
            'items.*' => 'string|max:255',
            'beds' => 'nullable|array|required_if:has_beds,1',
            'beds.*.name' => 'required_with:beds|string|max:255',
            'beds.*.rent_price' => 'nullable|numeric|min:0',
            'beds.*.rent_duration' => 'nullable|in:monthly,semester',
            'beds.*.semester_months' => 'nullable|integer|min:1|required_if:beds.*.rent_duration,semester',
            'beds.*.payment_frequency' => 'nullable|in:one_month,two_months,three_months,four_months,five_months,six_months|required_if:beds.*.rent_duration,monthly',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('rooms', 'public');
        }

        $hasBeds = $request->has('has_beds') && $request->has_beds;
        
        $room = Room::create([
            'block_id' => $request->block_id,
            'name' => $request->name,
            'image' => $imagePath,
            'location' => $request->location,
            'description' => $request->description,
            'has_beds' => $hasBeds,
            // Set pricing only if room has no beds
            'rent_price' => $hasBeds ? null : ($request->rent_price ?? null),
            'rent_duration' => $hasBeds ? null : ($request->rent_duration ?? null),
            'semester_months' => $hasBeds ? null : (($request->rent_duration ?? null) === 'semester' ? ($request->semester_months ?? null) : null),
            'payment_frequency' => $hasBeds ? null : (($request->rent_duration ?? null) === 'monthly' ? ($request->payment_frequency ?? null) : null),
        ]);

        // Add items
        if ($request->has('items') && is_array($request->items)) {
            foreach ($request->items as $itemName) {
                if (!empty($itemName)) {
                    RoomItem::create([
                        'room_id' => $room->id,
                        'item_name' => $itemName,
                    ]);
                }
            }
        }

        // Add beds
        if ($request->has_beds && $request->has('beds') && is_array($request->beds)) {
            foreach ($request->beds as $bed) {
                if (!empty($bed['name'])) {
                    Bed::create([
                        'room_id' => $room->id,
                        'name' => $bed['name'],
                        'rent_price' => $bed['rent_price'] ?? null,
                        'rent_duration' => $bed['rent_duration'] ?? null,
                        'semester_months' => ($bed['rent_duration'] ?? null) === 'semester' ? ($bed['semester_months'] ?? null) : null,
                        'payment_frequency' => ($bed['rent_duration'] ?? null) === 'monthly' ? ($bed['payment_frequency'] ?? null) : null,
                        'status' => 'free',
                    ]);
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Room added successfully!',
            'room' => $room->load(['block', 'beds', 'items'])
        ]);
    }

    /**
     * Store multiple rooms at once
     */
    private function storeMultipleRooms(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'block_id' => 'required|exists:blocks,id',
            'rooms' => 'required|array|min:1',
            'rooms.*.name' => 'required|string|max:255',
            'rooms.*.image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'rooms.*.location' => 'nullable|string|max:255',
            'rooms.*.description' => 'nullable|string',
            'rooms.*.has_beds' => 'boolean',
            'rooms.*.rent_price' => 'nullable|numeric|min:0|required_without:rooms.*.beds',
            'rooms.*.rent_duration' => 'nullable|in:monthly,semester|required_with:rooms.*.rent_price',
            'rooms.*.semester_months' => 'nullable|integer|min:1|required_if:rooms.*.rent_duration,semester',
            'rooms.*.payment_frequency' => 'nullable|in:one_month,two_months,three_months,four_months,five_months,six_months|required_if:rooms.*.rent_duration,monthly',
            'rooms.*.items' => 'nullable|array',
            'rooms.*.items.*' => 'string|max:255',
            'rooms.*.beds' => 'nullable|array',
            'rooms.*.beds.*.name' => 'required_with:rooms.*.beds|string|max:255',
            'rooms.*.beds.*.rent_price' => 'nullable|numeric|min:0',
            'rooms.*.beds.*.rent_duration' => 'nullable|in:monthly,semester',
            'rooms.*.beds.*.semester_months' => 'nullable|integer|min:1|required_if:rooms.*.beds.*.rent_duration,semester',
            'rooms.*.beds.*.payment_frequency' => 'nullable|in:one_month,two_months,three_months,four_months,five_months,six_months|required_if:rooms.*.beds.*.rent_duration,monthly',
        ]);

        if ($validator->fails()) {
            Log::error('Validation failed for multiple rooms', [
                'errors' => $validator->errors()->toArray(),
                'request_keys' => array_keys($request->all())
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Validation failed. Please check your input.',
                'errors' => $validator->errors()
            ], 422);
        }

        $createdRooms = [];
        
        foreach ($request->rooms as $roomIndex => $roomData) {
            try {
                $imagePath = null;
                // Handle file upload for each room
                if ($request->hasFile("rooms.{$roomIndex}.image")) {
                    try {
                        $imagePath = $request->file("rooms.{$roomIndex}.image")->store('rooms', 'public');
                    } catch (\Exception $e) {
                        Log::error('Error uploading room image: ' . $e->getMessage());
                    }
                }

                $hasBeds = isset($roomData['has_beds']) && ($roomData['has_beds'] == '1' || $roomData['has_beds'] === true);
                
                $room = Room::create([
                    'block_id' => $request->block_id,
                    'name' => $roomData['name'] ?? '',
                    'image' => $imagePath,
                    'location' => $roomData['location'] ?? null,
                    'description' => $roomData['description'] ?? null,
                    'has_beds' => $hasBeds,
                    // Set pricing only if room has no beds
                    'rent_price' => $hasBeds ? null : ($roomData['rent_price'] ?? null),
                    'rent_duration' => $hasBeds ? null : ($roomData['rent_duration'] ?? null),
                    'semester_months' => $hasBeds ? null : (($roomData['rent_duration'] ?? null) === 'semester' ? ($roomData['semester_months'] ?? null) : null),
                    'payment_frequency' => $hasBeds ? null : (($roomData['rent_duration'] ?? null) === 'monthly' ? ($roomData['payment_frequency'] ?? null) : null),
                ]);
            } catch (\Exception $e) {
                \Log::error('Error creating room: ' . $e->getMessage(), [
                    'roomIndex' => $roomIndex,
                    'roomData' => $roomData
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Error creating room: ' . $e->getMessage(),
                    'errors' => ['rooms' => ['Error creating room at index ' . $roomIndex . ': ' . $e->getMessage()]]
                ], 500);
            }

            // Add items
            if (isset($roomData['items']) && is_array($roomData['items'])) {
                foreach ($roomData['items'] as $itemName) {
                    if (!empty($itemName)) {
                        RoomItem::create([
                            'room_id' => $room->id,
                            'item_name' => $itemName,
                        ]);
                    }
                }
            }

            // Add beds
            if (isset($roomData['has_beds']) && $roomData['has_beds'] && isset($roomData['beds']) && is_array($roomData['beds'])) {
                foreach ($roomData['beds'] as $bed) {
                    if (!empty($bed['name'])) {
                        Bed::create([
                            'room_id' => $room->id,
                            'name' => $bed['name'],
                            'rent_price' => $bed['rent_price'] ?? null,
                            'rent_duration' => $bed['rent_duration'] ?? null,
                            'semester_months' => ($bed['rent_duration'] ?? null) === 'semester' ? ($bed['semester_months'] ?? null) : null,
                            'payment_frequency' => ($bed['rent_duration'] ?? null) === 'monthly' ? ($bed['payment_frequency'] ?? null) : null,
                            'status' => 'free',
                        ]);
                    }
                }
            }

            $createdRooms[] = $room->load(['block', 'beds', 'items']);
        }

        if (count($createdRooms) > 0) {
            return response()->json([
                'success' => true,
                'message' => count($createdRooms) . ' room(s) added successfully!',
                'rooms' => $createdRooms
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'No rooms were created. Please check your input.',
                'errors' => ['rooms' => ['No valid rooms to create']]
            ], 422);
        }
    }

    /**
     * Add bed to existing room (AJAX)
     */
    public function addBed(Request $request, Room $room)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'rent_price' => 'nullable|numeric|min:0',
            'rent_duration' => 'nullable|in:monthly,semester',
            'semester_months' => 'nullable|integer|min:1|required_if:rent_duration,semester',
            'payment_frequency' => 'nullable|in:one_month,two_months,three_months,four_months,five_months,six_months|required_if:rent_duration,monthly',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $bed = Bed::create([
            'room_id' => $room->id,
            'name' => $request->name,
            'rent_price' => $request->rent_price ?? null,
            'rent_duration' => $request->rent_duration ?? null,
            'semester_months' => ($request->rent_duration ?? null) === 'semester' ? ($request->semester_months ?? null) : null,
            'payment_frequency' => ($request->rent_duration ?? null) === 'monthly' ? ($request->payment_frequency ?? null) : null,
            'status' => 'free',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Bed added successfully!',
            'bed' => $bed
        ]);
    }

    /**
     * Remove bed from room (AJAX)
     */
    public function removeBed(Bed $bed)
    {
        $bed->delete();

        return response()->json([
            'success' => true,
            'message' => 'Bed removed successfully!'
        ]);
    }

    /**
     * Display the specified room
     */
    public function show(Room $room)
    {
        return response()->json($room->load(['block', 'beds', 'items']));
    }

    /**
     * Update the specified room
     */
    public function update(Request $request, Room $room)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'location' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        if ($request->hasFile('image')) {
            if ($room->image) {
                Storage::disk('public')->delete($room->image);
            }
            $imagePath = $request->file('image')->store('rooms', 'public');
            $room->image = $imagePath;
        }

        $room->name = $request->name;
        $room->location = $request->location;
        $room->save();

        return response()->json([
            'success' => true,
            'message' => 'Room updated successfully!',
            'room' => $room->load(['block', 'beds', 'items'])
        ]);
    }

    /**
     * Remove the specified room
     */
    public function destroy(Room $room)
    {
        if ($room->image) {
            Storage::disk('public')->delete($room->image);
        }
        $room->delete();

        return response()->json([
            'success' => true,
            'message' => 'Room deleted successfully!'
        ]);
    }
}
