<?php

namespace App\Http\Controllers;

use App\Models\LandingPageSetting;
use App\Models\Contact;
use App\Models\HostelDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = LandingPageSetting::all()->pluck('value', 'key')->toArray();
        
        // Default values if not set
        $defaults = [
            'hero_image' => asset('landing pages/img/hero.jpg'),
            'about_image' => asset('landing pages/img/about.jpg'),
            'primary_color' => '#1e3c72',
            'secondary_color' => '#2a5298',
            'light_color' => '#EFF5F9',
            'dark_color' => '#1D2A4D',
            'hostel_name' => 'ISACK HOSTEL',
            'contact_address' => '123 Hostel Street',
            'contact_city' => 'Dar es Salaam, Tanzania',
            'contact_phone1' => '+255 XXX XXX XXX',
            'contact_phone2' => '+255 XXX XXX XXX',
            'contact_email1' => 'info@isackhostel.com',
            'contact_email2' => 'bookings@isackhostel.com',
            'whatsapp_number' => '+255 XXX XXX XXX',
            'booking_timeout_hours' => '24',
        ];

        // Process image paths - store both raw path and display URL
        $rawSettings = LandingPageSetting::all()->pluck('value', 'key')->toArray();
        
        foreach (['hero_image', 'about_image'] as $imageKey) {
            if (isset($rawSettings[$imageKey]) && $rawSettings[$imageKey]) {
                // Store raw path for checking if uploaded
                $settings[$imageKey . '_raw'] = $rawSettings[$imageKey];
                // Convert to display URL
                $settings[$imageKey] = asset('storage/' . $rawSettings[$imageKey]);
            } else {
                $settings[$imageKey] = $defaults[$imageKey];
                $settings[$imageKey . '_raw'] = null;
            }
        }

        // Merge color defaults
        foreach (['primary_color', 'secondary_color', 'light_color', 'dark_color'] as $colorKey) {
            if (!isset($settings[$colorKey]) || !$settings[$colorKey]) {
                $settings[$colorKey] = $defaults[$colorKey];
            }
        }

        // Merge hostel name - get from database or use default
        if (isset($rawSettings['hostel_name']) && !empty($rawSettings['hostel_name'])) {
            $settings['hostel_name'] = $rawSettings['hostel_name'];
        } else {
            $settings['hostel_name'] = $defaults['hostel_name'];
        }

        // Get contact information from contacts table
        $contact = Contact::getContact();
        $settings['contact_address'] = $contact->contact_address ?? $defaults['contact_address'];
        $settings['contact_city'] = $contact->contact_city ?? $defaults['contact_city'];
        $settings['contact_phone1'] = $contact->contact_phone1 ?? $defaults['contact_phone1'];
        $settings['contact_phone2'] = $contact->contact_phone2 ?? $defaults['contact_phone2'];
        $settings['contact_email1'] = $contact->contact_email1 ?? $defaults['contact_email1'];
        $settings['contact_email2'] = $contact->contact_email2 ?? $defaults['contact_email2'];
        $settings['whatsapp_number'] = $contact->whatsapp_number ?? $defaults['whatsapp_number'];
        $settings['booking_timeout_hours'] = $contact->booking_timeout_hours ?? $defaults['booking_timeout_hours'];
        $settings['booking_timeout_unit'] = $contact->booking_timeout_unit ?? 'hours';
        $settings['booking_timeout_value'] = $contact->booking_timeout_value ?? ($contact->booking_timeout_hours ?? 24);

        // Get hostel details
        $hostelDetail = HostelDetail::getHostelDetail();
        $settings['hostel_name'] = $hostelDetail->hostel_name ?? 'ISACK HOSTEL';
        $settings['hostel_description'] = $hostelDetail->description ?? null;
        if ($hostelDetail->logo) {
            $settings['hostel_logo'] = asset('storage/' . $hostelDetail->logo);
            $settings['hostel_logo_raw'] = $hostelDetail->logo;
        } else {
            $settings['hostel_logo'] = null;
            $settings['hostel_logo_raw'] = null;
        }

        return view('settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        \Log::info('Settings update request received', [
            'ajax' => $request->ajax(),
            'wantsJson' => $request->wantsJson(),
            'isJson' => $request->expectsJson(),
            'headers' => $request->headers->all(),
            'data_keys' => array_keys($request->all())
        ]);
        
        try {
            $request->validate([
                'hero_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'about_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'primary_color' => 'nullable|string|max:7',
                'secondary_color' => 'nullable|string|max:7',
                'light_color' => 'nullable|string|max:7',
                'dark_color' => 'nullable|string|max:7',
                'hostel_name' => 'nullable|string|max:255',
                'contact_address' => 'nullable|string|max:255',
                'contact_city' => 'nullable|string|max:255',
                'contact_phone1' => 'nullable|string|max:50',
                'contact_phone2' => 'nullable|string|max:50',
                'contact_email1' => 'nullable|email|max:255',
                'contact_email2' => 'nullable|email|max:255',
                'whatsapp_number' => 'nullable|string|max:50',
                'booking_timeout_hours' => 'nullable|integer|min:1|max:168',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;
        }

        DB::beginTransaction();
        try {
            // Handle image uploads
            if ($request->hasFile('hero_image')) {
                $heroPath = $request->file('hero_image')->store('landing-page', 'public');
                LandingPageSetting::setValue('hero_image', $heroPath, 'image', 'images');
            }

            if ($request->hasFile('about_image')) {
                $aboutPath = $request->file('about_image')->store('landing-page', 'public');
                LandingPageSetting::setValue('about_image', $aboutPath, 'image', 'images');
            }

            // Handle colors
            if ($request->filled('primary_color')) {
                LandingPageSetting::setValue('primary_color', $request->primary_color, 'color', 'colors');
            }

            if ($request->filled('secondary_color')) {
                LandingPageSetting::setValue('secondary_color', $request->secondary_color, 'color', 'colors');
            }

            if ($request->filled('light_color')) {
                LandingPageSetting::setValue('light_color', $request->light_color, 'color', 'colors');
            }

            if ($request->filled('dark_color')) {
                LandingPageSetting::setValue('dark_color', $request->dark_color, 'color', 'colors');
            }

            // Hostel details are handled in separate method

            DB::commit();

            \Log::info('Settings updated successfully');

            // Always return JSON for AJAX requests
            if ($request->ajax() || $request->wantsJson() || $request->expectsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json([
                    'success' => true,
                    'message' => 'Settings updated successfully!'
                ], 200);
            }

            return redirect()->route('settings.index')
                ->with('success', 'Settings updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('Settings update failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            if ($request->ajax() || $request->wantsJson() || $request->expectsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json([
                    'success' => false,
                    'error' => 'Failed to update settings: ' . $e->getMessage()
                ], 422);
            }

            return redirect()->route('settings.index')
                ->with('error', 'Failed to update settings: ' . $e->getMessage());
        }
    }

    public function deleteImage(Request $request, $key)
    {
        $setting = LandingPageSetting::where('key', $key)->first();
        
        if ($setting && $setting->value) {
            Storage::disk('public')->delete($setting->value);
            $setting->delete();
        }

        return redirect()->route('settings.index')
            ->with('success', 'Image deleted successfully!');
    }

    /**
     * Update contact information
     */
    public function updateContact(Request $request)
    {
        try {
            $request->validate([
                'contact_address' => 'required|string|max:255',
                'contact_city' => 'required|string|max:255',
                'contact_phone1' => 'required|string|max:50',
                'contact_phone2' => 'nullable|string|max:50',
                'contact_email1' => 'required|email|max:255',
                'contact_email2' => 'nullable|email|max:255',
                'whatsapp_number' => 'required|string|max:50',
                'booking_timeout_unit' => 'required|in:hours,minutes',
                'booking_timeout_value' => 'required|integer|min:1',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;
        }

        DB::beginTransaction();
        try {
            // Prepare contact data
            $timeoutUnit = $request->input('booking_timeout_unit', 'hours');
            $timeoutValue = (int) $request->input('booking_timeout_value', 24);
            
            // Validate timeout value based on unit
            if ($timeoutUnit === 'minutes') {
                if ($timeoutValue < 1 || $timeoutValue > 10080) { // Max 7 days in minutes
                    $timeoutValue = 1440; // Default to 24 hours (1440 minutes)
                }
            } else {
                if ($timeoutValue < 1 || $timeoutValue > 168) { // Max 7 days
                    $timeoutValue = 24; // Default to 24 hours
                }
            }
            
            // Calculate hours for backward compatibility
            $timeoutHours = $timeoutUnit === 'minutes' ? round($timeoutValue / 60, 2) : $timeoutValue;
            
            $contactData = [
                'contact_address' => $request->input('contact_address'),
                'contact_city' => $request->input('contact_city'),
                'contact_phone1' => $request->input('contact_phone1'),
                'contact_phone2' => $request->input('contact_phone2', ''),
                'contact_email1' => $request->input('contact_email1'),
                'contact_email2' => $request->input('contact_email2', ''),
                'whatsapp_number' => $request->input('whatsapp_number'),
                'booking_timeout_hours' => (int) ceil($timeoutHours), // Keep for backward compatibility
                'booking_timeout_unit' => $timeoutUnit,
                'booking_timeout_value' => $timeoutValue,
            ];
            
            // Update single contact record (always one record, just update)
            Contact::updateContact($contactData);
            
            \Log::info('Contact information updated', [
                'contact_address' => $contactData['contact_address'],
                'contact_city' => $contactData['contact_city'],
                'contact_phone1' => $contactData['contact_phone1'],
                'whatsapp_number' => $contactData['whatsapp_number'],
                'booking_timeout_hours' => $contactData['booking_timeout_hours'],
            ]);

            DB::commit();

            if ($request->ajax() || $request->wantsJson() || $request->expectsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json([
                    'success' => true,
                    'message' => 'Contact information updated successfully!'
                ], 200);
            }

            return redirect()->route('settings.index')
                ->with('success', 'Contact information updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('Contact update failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            if ($request->ajax() || $request->wantsJson() || $request->expectsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json([
                    'success' => false,
                    'error' => 'Failed to update contact information: ' . $e->getMessage()
                ], 422);
            }

            return redirect()->route('settings.index')
                ->with('error', 'Failed to update contact information: ' . $e->getMessage());
        }
    }

    /**
     * Update hostel details
     */
    public function updateHostelDetail(Request $request)
    {
        try {
            $request->validate([
                'hostel_name' => 'required|string|max:255',
                'description' => 'nullable|string|max:1000',
                'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;
        }

        DB::beginTransaction();
        try {
            // Prepare hostel detail data
            $hostelData = [
                'hostel_name' => trim($request->input('hostel_name')),
                'description' => $request->input('description', ''),
            ];

            // Handle logo upload
            if ($request->hasFile('logo')) {
                $logoPath = $request->file('logo')->store('hostel', 'public');
                $hostelData['logo'] = $logoPath;
            }
            
            // Update single hostel detail record (always one record, just update)
            HostelDetail::updateHostelDetail($hostelData);
            
            \Log::info('Hostel details updated', [
                'hostel_name' => $hostelData['hostel_name'],
            ]);

            DB::commit();

            if ($request->ajax() || $request->wantsJson() || $request->expectsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json([
                    'success' => true,
                    'message' => 'Hostel details updated successfully!'
                ], 200);
            }

            return redirect()->route('settings.index')
                ->with('success', 'Hostel details updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('Hostel detail update failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            if ($request->ajax() || $request->wantsJson() || $request->expectsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json([
                    'success' => false,
                    'error' => 'Failed to update hostel details: ' . $e->getMessage()
                ], 422);
            }

            return redirect()->route('settings.index')
                ->with('error', 'Failed to update hostel details: ' . $e->getMessage());
        }
    }
}
