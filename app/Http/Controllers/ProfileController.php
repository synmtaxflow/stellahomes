<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Models\OwnerDetail;

class ProfileController extends Controller
{
    /**
     * Show the profile edit form
     */
    public function edit()
    {
        $user = Auth::user();
        $ownerDetail = $user->ownerDetail;
        return view('profile.edit', compact('ownerDetail'));
    }

    /**
     * Update the user's profile
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username,' . $user->id,
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $user->update([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
        ]);

        return redirect()->back()->with('success', 'Profile updated successfully!');
    }

    /**
     * Update the user's password
     */
    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        // Verify current password
        if (!Hash::check($request->current_password, $user->password)) {
            return redirect()->back()
                ->withErrors(['current_password' => 'Current password is incorrect.'])
                ->withInput();
        }

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        return redirect()->back()->with('success', 'Password updated successfully!');
    }

    /**
     * Update owner details
     */
    public function updateOwnerDetails(Request $request)
    {
        $user = Auth::user();

        // Only allow owners to update their details
        if ($user->role !== 'owner') {
            return redirect()->back()->with('error', 'Only owners can update these details.');
        }

        $validator = Validator::make($request->all(), [
            'phone_number' => 'nullable|string|max:50',
            'account_number' => 'nullable|string|max:100',
            'account_name' => 'nullable|string|max:255',
            'bank_name' => 'nullable|string|max:255',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'address' => 'nullable|string|max:500',
            'additional_info' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $ownerDetail = $user->ownerDetail;

        $data = [
            'phone_number' => $request->phone_number,
            'account_number' => $request->account_number,
            'account_name' => $request->account_name,
            'bank_name' => $request->bank_name,
            'address' => $request->address,
            'additional_info' => $request->additional_info,
        ];

        // Handle profile image upload
        if ($request->hasFile('profile_image')) {
            // Delete old image if exists
            if ($ownerDetail && $ownerDetail->profile_image) {
                Storage::disk('public')->delete($ownerDetail->profile_image);
            }
            $imagePath = $request->file('profile_image')->store('owner-profiles', 'public');
            $data['profile_image'] = $imagePath;
        }

        if ($ownerDetail) {
            $ownerDetail->update($data);
        } else {
            $data['user_id'] = $user->id;
            OwnerDetail::create($data);
        }

        return redirect()->back()->with('success', 'Owner details updated successfully!');
    }
}

