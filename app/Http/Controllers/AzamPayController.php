<?php

namespace App\Http\Controllers;

use App\Models\StudentControlNumber;
use App\Models\Payment;
use App\Models\Student;
use App\Models\Bed;
use App\Models\Room;
use App\Models\User;
use App\Models\OwnerDetail;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AzamPayController extends Controller
{
    private $secretKey;

    public function __construct()
    {
        $this->secretKey = config('services.azampay.secret_key');
    }

    /**
     * Verify hash from request
     */
    private function verifyHash(Request $request)
    {
        try {
            $rawBody = $request->getContent();
            
            if (empty($rawBody)) {
                return false;
            }

            $payload = json_decode($rawBody, true);

            if (!isset($payload['Hash']) || !isset($payload['Data'])) {
                return false;
            }

            $hashFromClient = $payload['Hash'];
            $data = $payload['Data'];

            // Ensure Amount is float (not string) to match generation
            if (isset($data['Amount'])) {
                $data['Amount'] = (float)$data['Amount'];
            }

            // Sort keys to ensure consistent order
            ksort($data);
            if (isset($data['AdditionalProperties']) && is_array($data['AdditionalProperties'])) {
                ksort($data['AdditionalProperties']);
            }

            $minifiedJson = json_encode($data, JSON_UNESCAPED_SLASHES);
            $serverHash = hash_hmac('sha256', $minifiedJson, $this->secretKey);

            return hash_equals($serverHash, $hashFromClient);
        } catch (\Exception $e) {
            Log::error('Hash verification error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Name Lookup API (Called by AzamPay Gateway)
     * POST /api/merchant/name-lookup
     */
    public function nameLookup(Request $request)
    {
        if (!$this->verifyHash($request)) {
            return response()->json([
                'Status' => 'Failure',
                'Message' => 'Invalid Hash'
            ], 400);
        }

        $data = $request->input('Data');
        $billIdentifier = $data['BillIdentifier'] ?? null;

        if (!$billIdentifier) {
            return response()->json([
                'Name' => null,
                'BillAmount' => 0,
                'BillIdentifier' => '',
                'Status' => 'Failure',
                'Message' => 'BillIdentifier is required',
                'StatusCode' => 1
            ], 400);
        }

        // Lookup control number in student_control_numbers table
        $controlNumber = StudentControlNumber::where('control_number', $billIdentifier)
            ->where('is_active', true)
            ->with('student')
            ->first();

        if (!$controlNumber) {
            return response()->json([
                'Name' => null,
                'BillAmount' => 0,
                'BillIdentifier' => $billIdentifier,
                'Status' => 'Failure',
                'Message' => 'Control number not found',
                'StatusCode' => 1
            ]);
        }

        // Check if control number has expired
        if ($controlNumber->expires_at) {
            $now = Carbon::now();
            $expiresAt = Carbon::parse($controlNumber->expires_at);
            
            if ($expiresAt->isPast() || $controlNumber->is_expired) {
                // Mark as expired if not already marked
                if (!$controlNumber->is_expired) {
                    $controlNumber->update(['is_expired' => true, 'is_active' => false]);
                }
                
                return response()->json([
                    'Name' => null,
                    'BillAmount' => 0,
                    'BillIdentifier' => $billIdentifier,
                    'Status' => 'Failure',
                    'Message' => 'Control number has expired. Please make a new booking.',
                    'StatusCode' => 2 // Status code 2 for expired
                ]);
            }
        }

        // Return remaining balance (starting_balance - total_paid)
        $remainingBalance = $controlNumber->remaining_balance;
        $studentName = $controlNumber->student->full_name ?? 'Unknown';

        return response()->json([
            'Name' => $studentName,
            'BillAmount' => (float)$remainingBalance, // Remaining balance to pay
            'BillIdentifier' => $billIdentifier,
            'Status' => 'Success',
            'Message' => 'Name found for the provided BillIdentifier.',
            'StatusCode' => 0
        ]);
    }

    /**
     * Payment API (Called by AzamPay Gateway when payment is made)
     * POST /api/merchant/payment
     */
    public function payment(Request $request)
    {
        if (!$this->verifyHash($request)) {
            return response()->json([
                'MerchantReferenceId' => null,
                'Status' => 'Failure',
                'StatusCode' => 1,
                'Message' => 'Invalid Hash'
            ], 400);
        }

        $data = $request->input('Data');

        $billIdentifier = $data['BillIdentifier'] ?? null;
        $amountPaid = (float)($data['Amount'] ?? 0);
        $fspRef = $data['FspReferenceId'] ?? null;
        $pgRef = $data['PgReferenceId'] ?? null;
        $billType = $data['BillType'] ?? null;
        $paymentDesc = $data['PaymentDesc'] ?? null;

        if (!$billIdentifier || $amountPaid <= 0) {
            return response()->json([
                'MerchantReferenceId' => null,
                'Status' => 'Failure',
                'StatusCode' => 1,
                'Message' => 'Invalid BillIdentifier or Amount'
            ], 400);
        }

        // Verify control number exists
        $controlNumber = StudentControlNumber::where('control_number', $billIdentifier)
            ->where('is_active', true)
            ->with('student')
            ->first();

        if (!$controlNumber) {
            return response()->json([
                'MerchantReferenceId' => null,
                'Status' => 'Failure',
                'StatusCode' => 1,
                'Message' => 'Control number not found'
            ]);
        }

        // Check if control number has expired
        if ($controlNumber->expires_at) {
            $now = Carbon::now();
            $expiresAt = Carbon::parse($controlNumber->expires_at);
            
            if ($expiresAt->isPast() || $controlNumber->is_expired) {
                // Mark as expired if not already marked
                if (!$controlNumber->is_expired) {
                    $controlNumber->update(['is_expired' => true, 'is_active' => false]);
                }
                
                return response()->json([
                    'MerchantReferenceId' => null,
                    'Status' => 'Failure',
                    'StatusCode' => 2,
                    'Message' => 'Control number has expired. Please make a new booking.'
                ]);
            }
        }

        $student = $controlNumber->student;
        if (!$student) {
            return response()->json([
                'MerchantReferenceId' => null,
                'Status' => 'Failure',
                'StatusCode' => 1,
                'Message' => 'Student not found for this control number'
            ]);
        }

        try {
            DB::beginTransaction();

            // Get rent price and details
            $rentPrice = 0;
            $rentDuration = null;
            $semesterMonths = null;
            $paymentFrequency = null;

            if ($student->bed_id) {
                $bed = Bed::find($student->bed_id);
                if ($bed) {
                    $rentPrice = $bed->rent_price ?? 0;
                    $rentDuration = $bed->rent_duration;
                    $semesterMonths = $bed->semester_months;
                    $paymentFrequency = $bed->payment_frequency;
                }
            } elseif ($student->room_id) {
                $room = Room::find($student->room_id);
                if ($room) {
                    $rentPrice = $room->rent_price ?? 0;
                    $rentDuration = $room->rent_duration;
                    $semesterMonths = $room->semester_months;
                    $paymentFrequency = $room->payment_frequency;
                }
            }

            // Get student's last payment to retrieve reserve amount
            $lastPayment = Payment::where('student_id', $student->id)
                ->where('status', 'completed')
                ->orderBy('payment_date', 'desc')
                ->orderBy('created_at', 'desc')
                ->first();

            $previousReserveAmount = $lastPayment ? ($lastPayment->reserve_amount ?? 0) : 0;
            $totalAvailable = $previousReserveAmount + $amountPaid;

            // Calculate payment period (start and end dates)
            $lastPaymentWithPeriod = Payment::where('student_id', $student->id)
                ->where('status', 'completed')
                ->whereNotNull('period_end_date')
                ->orderBy('period_end_date', 'desc')
                ->orderBy('payment_date', 'desc')
                ->orderBy('created_at', 'desc')
                ->first();

            // Set period start date
            if ($lastPaymentWithPeriod && $lastPaymentWithPeriod->period_end_date) {
                $periodStartDate = Carbon::parse($lastPaymentWithPeriod->period_end_date)->addDay();
            } else {
                // First payment - use check-in date
                if ($student->check_in_date) {
                    $periodStartDate = Carbon::parse($student->check_in_date);
                } else {
                    $periodStartDate = Carbon::now();
                    // Update student's check-in date if not set
                    $student->update(['check_in_date' => $periodStartDate->format('Y-m-d')]);
                }
            }

            // Calculate payment amount to record and reserve amount
            $paymentAmountToRecord = 0;
            $newReserveAmount = 0;
            $periodEndDate = $periodStartDate->copy();

            if ($rentPrice > 0 && $totalAvailable > 0) {
                // Calculate how many months/semesters can be covered
                if ($rentDuration === 'semester' && $semesterMonths && $semesterMonths > 0) {
                    // For semester: rent_price is per semester
                    $semestersCovered = floor($totalAvailable / $rentPrice);
                    if ($semestersCovered > 0) {
                        $paymentAmountToRecord = $rentPrice * $semestersCovered;
                        $newReserveAmount = $totalAvailable - $paymentAmountToRecord;
                        $periodEndDate = $periodStartDate->copy()->addMonths($semesterMonths * $semestersCovered);
                    } else {
                        $paymentAmountToRecord = 0;
                        $newReserveAmount = $totalAvailable;
                        $periodEndDate = $periodStartDate->copy()->addMonth();
                    }
                } else {
                    // For monthly: rent_price is per month
                    $monthsCovered = floor($totalAvailable / $rentPrice);
                    if ($monthsCovered > 0) {
                        $paymentAmountToRecord = $rentPrice * $monthsCovered;
                        $newReserveAmount = $totalAvailable - $paymentAmountToRecord;
                        $periodEndDate = $periodStartDate->copy()->addMonths($monthsCovered);
                    } else {
                        $paymentAmountToRecord = 0;
                        $newReserveAmount = $totalAvailable;
                        $periodEndDate = $periodStartDate->copy()->addMonth();
                    }
                }
            } else {
                $paymentAmountToRecord = 0;
                $newReserveAmount = $totalAvailable;
                $periodEndDate = $periodStartDate->copy()->addMonth();
            }

            // Generate merchant reference
            $merchantRef = Str::uuid()->toString();

            // Create payment record
            $paymentData = [
                'student_id' => $student->id,
                'amount' => $paymentAmountToRecord,
                'reserve_amount' => $newReserveAmount,
                'payment_date' => Carbon::now()->format('Y-m-d'),
                'payment_method' => 'bank',
                'status' => 'completed',
                'reference_number' => $fspRef ?? $pgRef ?? $merchantRef,
                'merchant_reference' => $merchantRef,
                'notes' => "AzamPay Payment - Control: {$billIdentifier}, FSP: {$fspRef}, PG: {$pgRef}",
            ];

            // Only add period information if we're recording a rent payment
            if ($paymentAmountToRecord > 0) {
                $paymentData['period_code'] = 'PAY-' . strtoupper(substr(md5($student->id . Carbon::now()->format('Y-m-d') . time()), 0, 8));
                $paymentData['period_start_date'] = $periodStartDate->format('Y-m-d');
                $paymentData['period_end_date'] = $periodEndDate->format('Y-m-d');
            }

            $payment = Payment::create($paymentData);

            // Update control number
            $controlNumber->total_paid += $amountPaid;
            $controlNumber->updateBalance();
            $controlNumber->save();

            // Update bed status to occupied if payment is completed and bed exists
            if ($student->bed_id) {
                $bed = Bed::find($student->bed_id);
                if ($bed && $bed->status === 'pending_payment') {
                    $bed->update(['status' => 'occupied']);
                }
            }

            // Get owner account details for SMS
            $owner = User::where('role', 'owner')->first();
            $ownerDetail = $owner ? OwnerDetail::where('user_id', $owner->id)->first() : null;
            
            // Send SMS to student with payment confirmation
            if ($student->phone && $ownerDetail) {
                try {
                    $smsService = new SmsService();
                    
                    $accountName = $ownerDetail->account_name ?? 'N/A';
                    $bankName = $ownerDetail->bank_name ?? 'N/A';
                    $accountNumber = $ownerDetail->account_number ?? 'N/A';
                    $referenceNumber = $payment->reference_number ?? $merchantRef;
                    
                    $paymentMessage = "Payment Received!\n";
                    $paymentMessage .= "Your payment of Tsh " . number_format($amountPaid, 0) . " has been received.\n";
                    $paymentMessage .= "Account Name: {$accountName}\n";
                    $paymentMessage .= "Bank: {$bankName}\n";
                    $paymentMessage .= "Account Number: {$accountNumber}\n";
                    $paymentMessage .= "Reference: {$referenceNumber}\n";
                    $paymentMessage .= "Control Number: {$billIdentifier}\n";
                    $paymentMessage .= "Thank you!";
                    
                    $smsService->sendSms($student->phone, $paymentMessage);
                    
                    Log::info('Payment confirmation SMS sent', [
                        'student_id' => $student->id,
                        'phone' => $student->phone,
                        'amount' => $amountPaid
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to send payment confirmation SMS: ' . $e->getMessage(), [
                        'student_id' => $student->id,
                        'phone' => $student->phone
                    ]);
                    // Don't fail the payment if SMS fails
                }
            }

            DB::commit();

            Log::info('AzamPay payment processed successfully', [
                'control_number' => $billIdentifier,
                'amount' => $amountPaid,
                'merchant_ref' => $merchantRef,
                'student_id' => $student->id
            ]);

            return response()->json([
                'MerchantReferenceId' => $merchantRef,
                'Status' => 'Success',
                'StatusCode' => 0,
                'Message' => 'Payment successful'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('AzamPay payment error: ' . $e->getMessage(), [
                'control_number' => $billIdentifier,
                'amount' => $amountPaid,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'MerchantReferenceId' => null,
                'Status' => 'Failure',
                'StatusCode' => 1,
                'Message' => 'Payment processing failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Status Check API (Called by AzamPay Gateway to check payment status)
     * POST /api/merchant/status-check
     */
    public function statusCheck(Request $request)
    {
        $merchantRef = $request->input('MerchantReferenceId');

        if (!$merchantRef) {
            return response()->json([
                'MerchantReferenceId' => null,
                'Status' => 'Failure',
                'StatusCode' => 1,
                'Message' => 'MerchantReferenceId is required'
            ], 400);
        }

        // Find payment by merchant reference
        $payment = Payment::where('merchant_reference', $merchantRef)
            ->with('student')
            ->first();

        if (!$payment) {
            return response()->json([
                'MerchantReferenceId' => $merchantRef,
                'Status' => 'Failure',
                'StatusCode' => 1,
                'Message' => 'Payment not found'
            ]);
        }

        // Map payment status to AzamPay status format
        $status = 'Pending';
        if ($payment->status === 'completed') {
            $status = 'Success';
        } elseif ($payment->status === 'failed') {
            $status = 'Failure';
        }

        return response()->json([
            'MerchantReferenceId' => $merchantRef,
            'Status' => $status,
            'StatusCode' => $payment->status === 'completed' ? 0 : 1,
            'Message' => $payment->status === 'completed' 
                ? 'Payment completed successfully' 
                : ($payment->status === 'failed' ? 'Payment failed' : 'Payment is pending')
        ]);
    }
}
