<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Block;
use Illuminate\Support\Facades\Storage;

class BlockController extends Controller
{
    /**
     * Display a listing of blocks
     */
    public function index()
    {
        $blocks = Block::withCount('rooms')->orderBy('created_at', 'desc')->get();
        return view('blocks.index', compact('blocks'));
    }

    /**
     * Store a newly created block (AJAX)
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'type' => 'required|in:flat,normal',
            'floors' => 'nullable|integer|min:1|required_if:type,flat',
        ], [
            'floors.required_if' => 'Number of floors is required for flat type blocks.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('blocks', 'public');
        }

        $block = Block::create([
            'name' => $request->name,
            'image' => $imagePath,
            'type' => $request->type,
            'floors' => $request->type === 'flat' ? $request->floors : null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Block registered successfully!',
            'block' => $block
        ]);
    }

    /**
     * Display the specified block
     */
    public function show(Block $block)
    {
        $block->loadCount('rooms');
        return response()->json($block);
    }

    /**
     * Update the specified block
     */
    public function update(Request $request, Block $block)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'type' => 'required|in:flat,normal',
            'floors' => 'nullable|integer|min:1|required_if:type,flat',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        if ($request->hasFile('image')) {
            // Delete old image
            if ($block->image) {
                Storage::disk('public')->delete($block->image);
            }
            $imagePath = $request->file('image')->store('blocks', 'public');
            $block->image = $imagePath;
        }

        $block->name = $request->name;
        $block->type = $request->type;
        $block->floors = $request->type === 'flat' ? $request->floors : null;
        $block->save();

        return response()->json([
            'success' => true,
            'message' => 'Block updated successfully!',
            'block' => $block
        ]);
    }

    /**
     * Remove the specified block
     */
    public function destroy(Block $block)
    {
        if ($block->image) {
            Storage::disk('public')->delete($block->image);
        }
        $block->delete();

        return response()->json([
            'success' => true,
            'message' => 'Block deleted successfully!'
        ]);
    }
}
