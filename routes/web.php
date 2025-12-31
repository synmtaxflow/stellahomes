<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\BlockController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\RoomItemController;
use App\Http\Controllers\BedController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\RentScheduleController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\LandingPageController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\StudentDashboardController;
use App\Http\Controllers\SuggestionIncidenceController;
use App\Http\Controllers\TermsAndConditionController;
use App\Http\Controllers\ControlNumberController;
use App\Http\Controllers\AzamPayController;

// Landing Page (Public)
Route::get('/', [LandingPageController::class, 'index'])->name('landing');
Route::post('/book', [LandingPageController::class, 'book'])->name('landing.book');
Route::get('/api/terms/active', [TermsAndConditionController::class, 'getActive'])->name('terms.active');

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login')->middleware('guest');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('password.forgot')->middleware('guest');
Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->name('password.forgot')->middleware('guest');

// OTP Verification Routes
Route::get('/otp/verify', [AuthController::class, 'showOtpVerificationForm'])->name('otp.verify')->middleware('guest');
Route::post('/otp/verify', [AuthController::class, 'verifyOtp'])->name('otp.verify')->middleware('guest');
Route::post('/otp/resend', [AuthController::class, 'resendOtp'])->name('otp.resend')->middleware('guest');

// Protected Dashboard Routes
Route::middleware('auth')->group(function () {
    // Profile Routes
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/password/update', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
    Route::post('/profile/owner-details/update', [ProfileController::class, 'updateOwnerDetails'])->name('profile.owner-details.update');
    
    // Blocks Management Routes
    Route::resource('blocks', BlockController::class);
    Route::get('/blocks', [BlockController::class, 'index'])->name('blocks.index');
    Route::post('/blocks', [BlockController::class, 'store'])->name('blocks.store');
    
    // Rooms Management Routes
    Route::get('/rooms', [RoomController::class, 'index'])->name('rooms.index');
    Route::post('/rooms', [RoomController::class, 'store'])->name('rooms.store');
    Route::get('/rooms/{room}', [RoomController::class, 'show'])->name('rooms.show');
    Route::put('/rooms/{room}', [RoomController::class, 'update'])->name('rooms.update');
    Route::delete('/rooms/{room}', [RoomController::class, 'destroy'])->name('rooms.destroy');
    Route::post('/rooms/{room}/beds', [RoomController::class, 'addBed'])->name('rooms.beds.add');
    Route::delete('/rooms/beds/{bed}', [RoomController::class, 'removeBed'])->name('rooms.beds.remove');
    
    // Beds Management Routes
    Route::get('/beds/{bed}', [BedController::class, 'show'])->name('beds.show');
    Route::put('/beds/{bed}', [BedController::class, 'update'])->name('beds.update');
    
    // Room Items Routes
    Route::get('/room-items/{roomItem}', [RoomItemController::class, 'show'])->name('room-items.show');
    Route::put('/room-items/{roomItem}', [RoomItemController::class, 'update'])->name('room-items.update');
    Route::delete('/room-items/{roomItem}', [RoomItemController::class, 'destroy'])->name('room-items.destroy');
    
    // Students Management Routes
    Route::get('/students', [StudentController::class, 'index'])->name('students.index');
    Route::post('/students', [StudentController::class, 'store'])->name('students.store');
    Route::get('/students/{student}', [StudentController::class, 'show'])->name('students.show');
    Route::put('/students/{student}', [StudentController::class, 'update'])->name('students.update');
    Route::post('/students/{student}/remove', [StudentController::class, 'remove'])->name('students.remove');
    Route::delete('/students/{student}', [StudentController::class, 'destroy'])->name('students.destroy');
    Route::get('/blocks/{block}/rooms', [StudentController::class, 'getRoomsByBlock'])->name('blocks.rooms');
    
    // Payments Management Routes
    Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::get('/payments/manual', [PaymentController::class, 'manualPayments'])->name('payments.manual');
    Route::post('/payments', [PaymentController::class, 'store'])->name('payments.store');
    Route::get('/payments/{payment}', [PaymentController::class, 'show'])->name('payments.show');
    Route::put('/payments/{payment}', [PaymentController::class, 'update'])->name('payments.update');
    Route::delete('/payments/{payment}', [PaymentController::class, 'destroy'])->name('payments.destroy');
    Route::get('/students/{student}/payment-details', [PaymentController::class, 'getStudentDetails'])->name('students.payment-details');
    
    // Rent Schedules Management Routes
    Route::get('/rent-schedules', [RentScheduleController::class, 'index'])->name('rent-schedules.index');
    Route::post('/rent-schedules', [RentScheduleController::class, 'store'])->name('rent-schedules.store');
    Route::get('/rent-schedules/{rentSchedule}', [RentScheduleController::class, 'show'])->name('rent-schedules.show');
    Route::put('/rent-schedules/{rentSchedule}', [RentScheduleController::class, 'update'])->name('rent-schedules.update');
    Route::delete('/rent-schedules/{rentSchedule}', [RentScheduleController::class, 'destroy'])->name('rent-schedules.destroy');
    Route::get('/rent-schedules/active', [RentScheduleController::class, 'getSchedule'])->name('rent-schedules.get');
    
    // Reports Routes
    Route::get('/reports/rent-status', [ReportController::class, 'rentStatus'])->name('reports.rent-status');
    Route::get('/reports/rent-status/export-pdf', [ReportController::class, 'rentStatusExportPdf'])->name('reports.rent-status.export-pdf');
    Route::get('/reports/rent-status/export-excel', [ReportController::class, 'rentStatusExportExcel'])->name('reports.rent-status.export-excel');
    Route::get('/reports/payment-report', [ReportController::class, 'paymentReport'])->name('reports.payment-report');
    
    // Bookings Routes (Owner only)
    Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index');
    Route::post('/bookings/{booking}/record-payment', [BookingController::class, 'recordPayment'])->name('bookings.record-payment');
    Route::delete('/bookings/{booking}/cancel', [BookingController::class, 'cancel'])->name('bookings.cancel');
    
    // Settings Routes (Owner only)
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');
    Route::post('/settings/contact', [SettingsController::class, 'updateContact'])->name('settings.contact.update');
    Route::post('/settings/hostel-detail', [SettingsController::class, 'updateHostelDetail'])->name('settings.hostel-detail.update');
    Route::post('/settings/images/{key}/delete', [SettingsController::class, 'deleteImage'])->name('settings.delete-image');
    
    // User Management Routes (Owner only)
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');
    
    // Dashboard Routes
    Route::get('/dashboard/owner', function () {
        if (Auth::user()->role !== 'owner') {
            return redirect('/dashboard/' . Auth::user()->role);
        }
        return app(UserController::class)->index();
    })->name('dashboard.owner');
    
    Route::get('/dashboard/matron', function () {
        if (!in_array(Auth::user()->role, ['matron', 'patron'])) {
            return redirect('/dashboard/' . Auth::user()->role);
        }
        return view('dashboard.matron');
    })->name('dashboard.matron');
    
    // Student Dashboard Routes
    Route::get('/dashboard/student', [StudentDashboardController::class, 'index'])->name('dashboard.student');
    Route::post('/dashboard/student/upload-profile-picture', [StudentDashboardController::class, 'uploadProfilePicture'])->name('student.upload-profile-picture');
    
    // Suggestions and Incidences Routes
    Route::post('/suggestions', [SuggestionIncidenceController::class, 'storeSuggestion'])->name('suggestions.store');
    Route::post('/incidences', [SuggestionIncidenceController::class, 'storeIncidence'])->name('incidences.store');
    Route::get('/student/suggestions-incidences', [SuggestionIncidenceController::class, 'getStudentSuggestions'])->name('student.suggestions-incidences');
    
    // Owner Routes for Suggestions and Incidences
    Route::get('/suggestions', [SuggestionIncidenceController::class, 'getSuggestions'])->name('suggestions.index');
    Route::get('/incidences', [SuggestionIncidenceController::class, 'getIncidences'])->name('incidences.index');
    Route::post('/suggestions/{id}/read', [SuggestionIncidenceController::class, 'markSuggestionRead'])->name('suggestions.read');
    Route::post('/incidences/{id}/resolve', [SuggestionIncidenceController::class, 'markIncidenceResolved'])->name('incidences.resolve');
    
    // Terms and Conditions Routes (Owner only)
    Route::resource('terms', TermsAndConditionController::class);
    Route::post('/terms/{id}/toggle-active', [TermsAndConditionController::class, 'toggleActive'])->name('terms.toggle-active');
    
    // Control Numbers Routes (Owner only)
    Route::get('/control-numbers', [ControlNumberController::class, 'index'])->name('control-numbers.index');
    Route::get('/control-numbers/{id}', [ControlNumberController::class, 'show'])->name('control-numbers.show');
    Route::post('/control-numbers/generate-hash', [ControlNumberController::class, 'generateHash'])->name('control-numbers.generate-hash');
    Route::post('/control-numbers/generate-payment-hash', [ControlNumberController::class, 'generatePaymentHash'])->name('control-numbers.generate-payment-hash');
    
    // Default dashboard redirect
    Route::get('/dashboard', function () {
        $role = Auth::user()->role;
        return redirect('/dashboard/' . $role);
    });
});
