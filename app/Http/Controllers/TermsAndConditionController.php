<?php

namespace App\Http\Controllers;

use App\Models\TermsAndCondition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TermsAndConditionController extends Controller
{
    /**
     * Display a listing of terms and conditions
     */
    public function index()
    {
        if (Auth::user()->role !== 'owner') {
            return redirect()->back()->with('error', 'Unauthorized.');
        }

        $terms = TermsAndCondition::with('creator')
            ->orderBy('created_at', 'desc')
            ->get();

        $activeTerms = TermsAndCondition::getActive();

        return view('terms.index', compact('terms', 'activeTerms'));
    }

    /**
     * Show the form for creating new terms
     */
    public function create()
    {
        if (Auth::user()->role !== 'owner') {
            return redirect()->back()->with('error', 'Unauthorized.');
        }

        return view('terms.create');
    }

    /**
     * Store newly created terms (single or multiple)
     */
    public function store(Request $request)
    {
        if (Auth::user()->role !== 'owner') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.'
            ], 403);
        }

        // Handle multiple terms
        if ($request->has('terms') && is_array($request->terms)) {
            $created = [];
            $hasActive = false;

            foreach ($request->terms as $termData) {
                if (empty($termData['content']) || empty($termData['version'])) {
                    continue;
                }

                // If this is the first active term, deactivate all existing
                if (isset($termData['is_active']) && $termData['is_active'] && !$hasActive) {
                    TermsAndCondition::where('is_active', true)->update(['is_active' => false]);
                    $hasActive = true;
                }

                $term = TermsAndCondition::create([
                    'content' => $termData['content'],
                    'version' => $termData['version'],
                    'is_active' => isset($termData['is_active']) && $termData['is_active'] ? true : false,
                    'created_by' => Auth::id(),
                ]);

                $created[] = $term;
            }

            return response()->json([
                'success' => true,
                'message' => count($created) . ' Terms and Conditions created successfully!',
                'count' => count($created)
            ]);
        }

        // Handle single term (backward compatibility)
        $request->validate([
            'content' => 'required|string',
            'version' => 'required|string|max:50',
        ]);

        // Deactivate all existing terms if this one is active
        if ($request->has('is_active') && $request->is_active) {
            TermsAndCondition::where('is_active', true)->update(['is_active' => false]);
        }

        // Create new terms
        TermsAndCondition::create([
            'content' => $request->content,
            'version' => $request->version,
            'is_active' => $request->has('is_active') ? true : false,
            'created_by' => Auth::id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Terms and Conditions created successfully!'
        ]);
    }

    /**
     * Show the form for editing terms
     */
    public function edit($id)
    {
        if (Auth::user()->role !== 'owner') {
            return redirect()->back()->with('error', 'Unauthorized.');
        }

        $terms = TermsAndCondition::findOrFail($id);
        return view('terms.edit', compact('terms'));
    }

    /**
     * Update existing terms
     */
    public function update(Request $request, $id)
    {
        if (Auth::user()->role !== 'owner') {
            return redirect()->back()->with('error', 'Unauthorized.');
        }

        $request->validate([
            'content' => 'required|string',
            'version' => 'required|string|max:50',
        ]);

        $terms = TermsAndCondition::findOrFail($id);

        // If activating these terms, deactivate others
        if ($request->has('is_active') && $request->is_active) {
            TermsAndCondition::where('id', '!=', $id)
                ->where('is_active', true)
                ->update(['is_active' => false]);
        }

        $terms->update([
            'content' => $request->content,
            'version' => $request->version,
            'is_active' => $request->has('is_active') ? true : false,
        ]);

        return redirect()->route('terms.index')
            ->with('success', 'Terms and Conditions updated successfully!');
    }

    /**
     * Toggle active status
     */
    public function toggleActive($id)
    {
        if (Auth::user()->role !== 'owner') {
            return redirect()->back()->with('error', 'Unauthorized.');
        }

        $terms = TermsAndCondition::findOrFail($id);

        if ($terms->is_active) {
            $terms->update(['is_active' => false]);
            $message = 'Terms and Conditions deactivated.';
        } else {
            // Deactivate all others first
            TermsAndCondition::where('id', '!=', $id)
                ->where('is_active', true)
                ->update(['is_active' => false]);
            
            $terms->update(['is_active' => true]);
            $message = 'Terms and Conditions activated.';
        }

        return redirect()->route('terms.index')
            ->with('success', $message);
    }

    /**
     * Delete terms
     */
    public function destroy($id)
    {
        if (Auth::user()->role !== 'owner') {
            return redirect()->back()->with('error', 'Unauthorized.');
        }

        $terms = TermsAndCondition::findOrFail($id);
        $terms->delete();

        return redirect()->route('terms.index')
            ->with('success', 'Terms and Conditions deleted successfully!');
    }

    /**
     * Get active terms (for API/public use)
     */
    public function getActive()
    {
        $terms = TermsAndCondition::getActive();
        
        if (!$terms) {
            return response()->json([
                'success' => false,
                'message' => 'No active terms and conditions found.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'terms' => $terms
        ]);
    }
}
