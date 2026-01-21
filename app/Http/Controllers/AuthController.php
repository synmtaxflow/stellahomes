<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Student;
use App\Models\OtpVerification;
use App\Services\SmsService;
use Carbon\Carbon;

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
            // If user is owner, require OTP verification
            if ($user->role === 'owner') {
                // Get phone number
                $phoneNumber = $this->getPhoneNumberByRole($user);
                
                if (!$phoneNumber) {
                    return back()->withErrors([
                        'username' => 'Phone number not found for your account. Please contact administrator.',
                    ])->onlyInput('username');
                }

                // Create OTP and send via SMS
                $otp = OtpVerification::createForUser($user->id, $phoneNumber, 10);
                
                $smsService = new SmsService();
                $message = "Your OTP code for login is: {$otp->otp_code}. This code will expire in 10 minutes. Do not share this code with anyone.";
                
                $smsResult = $smsService->sendSms($phoneNumber, $message);

                // Store user ID in session for OTP verification
                $request->session()->put('otp_user_id', $user->id);
                $request->session()->put('otp_verification_id', $otp->id);

                return redirect()->route('otp.verify')->with('success', 'OTP code has been sent to your phone number. Please enter it to complete login.');
            }

            // For non-owner users, login directly
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
        
        // Update user password (Laravel will auto-hash it due to 'hashed' cast in User model)
        $user->password = $newPassword;
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

    /**
     * Show OTP verification form
     */
    public function showOtpVerificationForm()
    {
        if (!session('otp_user_id')) {
            return redirect()->route('login')->withErrors([
                'username' => 'Please login first to receive OTP code.',
            ]);
        }

        $user = User::find(session('otp_user_id'));
        if (!$user) {
            session()->forget(['otp_user_id', 'otp_verification_id']);
            return redirect()->route('login')->withErrors([
                'username' => 'User not found. Please try again.',
            ]);
        }

        $phoneNumber = $this->getPhoneNumberByRole($user);
        $maskedPhone = $phoneNumber ? substr($phoneNumber, 0, 4) . '****' . substr($phoneNumber, -4) : 'N/A';

        return view('auth.otp-verify', [
            'maskedPhone' => $maskedPhone,
        ]);
    }

    /**
     * Verify OTP code
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp_code' => 'required|string|size:6',
        ]);

        $userId = session('otp_user_id');
        $otpVerificationId = session('otp_verification_id');

        if (!$userId || !$otpVerificationId) {
            return redirect()->route('login')->withErrors([
                'username' => 'Session expired. Please login again.',
            ]);
        }

        $user = User::find($userId);
        if (!$user) {
            session()->forget(['otp_user_id', 'otp_verification_id']);
            return redirect()->route('login')->withErrors([
                'username' => 'User not found. Please try again.',
            ]);
        }

        $otp = OtpVerification::where('id', $otpVerificationId)
            ->where('user_id', $userId)
            ->where('is_verified', false)
            ->first();

        if (!$otp) {
            return back()->withErrors([
                'otp_code' => 'Invalid or expired OTP code. Please request a new one.',
            ])->withInput();
        }

        if ($otp->isExpired()) {
            return back()->withErrors([
                'otp_code' => 'OTP code has expired. Please request a new one.',
            ])->withInput();
        }

        if ($otp->otp_code !== $request->otp_code) {
            return back()->withErrors([
                'otp_code' => 'Invalid OTP code. Please try again.',
            ])->withInput();
        }

        // OTP is valid, mark as verified and login user
        $otp->markAsVerified();

        Auth::login($user, $request->filled('remember'));
        $request->session()->regenerate();
        
        // Clear OTP session data
        $request->session()->forget(['otp_user_id', 'otp_verification_id']);

        return redirect()->intended($this->redirectTo($user->role))->with('success', 'Login successful!');
    }

    /**
     * Resend OTP code
     */
    public function resendOtp(Request $request)
    {
        $userId = session('otp_user_id');

        if (!$userId) {
            return redirect()->route('login')->withErrors([
                'username' => 'Session expired. Please login again.',
            ]);
        }

        $user = User::find($userId);
        if (!$user) {
            session()->forget(['otp_user_id', 'otp_verification_id']);
            return redirect()->route('login')->withErrors([
                'username' => 'User not found. Please try again.',
            ]);
        }

        // Check if last OTP was sent less than 2 minutes ago
        $lastOtp = OtpVerification::where('user_id', $userId)
            ->where('is_verified', false)
            ->orderBy('created_at', 'desc')
            ->first();

        if ($lastOtp && $lastOtp->created_at->diffInSeconds(now()) < 120) {
            $remainingSeconds = 120 - $lastOtp->created_at->diffInSeconds(now());
            return back()->withErrors([
                'otp_code' => "Please wait {$remainingSeconds} seconds before requesting a new OTP code.",
            ]);
        }

        // Get phone number
        $phoneNumber = $this->getPhoneNumberByRole($user);
        
        if (!$phoneNumber) {
            return back()->withErrors([
                'otp_code' => 'Phone number not found for your account. Please contact administrator.',
            ]);
        }

        // Create new OTP and send via SMS
        $otp = OtpVerification::createForUser($user->id, $phoneNumber, 10);
        
        $smsService = new SmsService();
        $message = "Your OTP code for login is: {$otp->otp_code}. This code will expire in 10 minutes. Do not share this code with anyone.";
        
        $smsResult = $smsService->sendSms($phoneNumber, $message);

        // Update session with new OTP verification ID
        $request->session()->put('otp_verification_id', $otp->id);

        return redirect()->route('otp.verify')->with('success', 'New OTP code has been sent to your phone number.');
    }
}

