<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\RoomItem;

class RoomItemController extends Controller
{
    /**
     * Display the specified room item
     */
    public function show(RoomItem $roomItem)
    {
        return response()->json($roomItem->load(['room.block']));
    }

    /**
     * Update the specified room item
     */
    public function update(Request $request, RoomItem $roomItem)
    {
        $validator = Validator::make($request->all(), [
            'item_name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $roomItem->item_name = $request->item_name;
        $roomItem->save();

        return response()->json([
            'success' => true,
            'message' => 'Room item updated successfully!',
            'item' => $roomItem->load(['room.block'])
        ]);
    }

    /**
     * Remove the specified room item
     */
    public function destroy(RoomItem $roomItem)
    {
        $roomItem->delete();

        return response()->json([
            'success' => true,
            'message' => 'Room item deleted successfully!'
        ]);
    }
}

