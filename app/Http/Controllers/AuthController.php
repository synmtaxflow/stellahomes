<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Student;
use App\Services\SmsService;

class AuthController extends Controller
{
    /**
     * Show the login form
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Handle login request
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required',
        ]);

        // Try to authenticate using username
        $user = User::where('username', $credentials['username'])->first();

        if ($user && Hash::check($credentials['password'], $user->password)) {
            Auth::login($user, $request->filled('remember'));
            $request->session()->regenerate();
            
            // Redirect based on user role
            return redirect()->intended($this->redirectTo($user->role));
        }

        return back()->withErrors([
            'username' => 'The provided credentials do not match our records.',
        ])->onlyInput('username');
    }

    /**
     * Handle logout request
     */
    public function logout(Request $request)
    {
        Auth::logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect('/login');
    }

    /**
     * Get redirect path based on user role
     */
    protected function redirectTo($role)
    {
        switch ($role) {
            case 'owner':
                return '/dashboard/owner';
            case 'matron':
            case 'patron':
                return '/dashboard/matron';
            case 'student':
                return '/dashboard/student';
            default:
                return '/dashboard';
        }
    }

    /**
     * Show the forgot password form
     */
    public function showForgotPasswordForm()
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle forgot password request
     */
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
        ]);

        // Find user by username
        $user = User::where('username', $request->username)->first();

        if (!$user) {
            return back()->withErrors([
                'username' => 'Username not found in our records.',
            ])->onlyInput('username');
        }

        // Get phone number based on user role
        $phoneNumber = $this->getPhoneNumberByRole($user);

        if (!$phoneNumber) {
            return back()->withErrors([
                'username' => 'Phone number not found for your account. Please contact administrator.',
            ])->onlyInput('username');
        }

        // Generate a new temporary password
        $newPassword = $this->generateTemporaryPassword();
        
        // Update user password
        $user->password = Hash::make($newPassword);
        $user->save();

        // Send SMS with password
        $smsService = new SmsService();
        $message = "Your password has been reset. Your new password is: {$newPassword}. Please change it after logging in.";
        
        $smsResult = $smsService->sendSms($phoneNumber, $message);

        if ($smsResult['success']) {
            return back()->with('success', 'Password has been sent to your phone number via SMS. Please check your phone.');
        } else {
            // If SMS fails, still show success to user (security: don't reveal if phone number exists)
            // Log the error for admin
            \Log::error('SMS sending failed for forgot password', [
                'user_id' => $user->id,
                'username' => $user->username,
                'phone' => $phoneNumber,
                'error' => $smsResult['message']
            ]);
            
            return back()->with('success', 'Password reset request processed. If you don\'t receive SMS, please contact administrator.');
        }
    }

    /**
     * Get phone number based on user role
     */
    protected function getPhoneNumberByRole(User $user)
    {
        switch ($user->role) {
            case 'owner':
                // Get phone from owner_details
                $ownerDetail = $user->ownerDetail;
                return $ownerDetail ? $ownerDetail->phone_number : null;

            case 'matron':
            case 'patron':
                // Matrons/Patrons might also use owner_details table
                $ownerDetail = $user->ownerDetail;
                return $ownerDetail ? $ownerDetail->phone_number : null;

            case 'student':
                // Get phone from students table by matching email
                $student = Student::where('email', $user->email)->first();
                return $student ? $student->phone : null;

            default:
                return null;
        }
    }

    /**
     * Generate a temporary password
     */
    protected function generateTemporaryPassword($length = 8)
    {
        // Generate a random password with letters and numbers
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $password = '';
        for ($i = 0; $i < $length; $i++) {
            $password .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $password;
    }
}

