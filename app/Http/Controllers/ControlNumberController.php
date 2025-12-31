<?php

namespace App\Http\Controllers;

use App\Models\StudentControlNumber;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ControlNumberController extends Controller
{
    /**
     * Display all control numbers
     */
    public function index()
    {
        if (Auth::user()->role !== 'owner') {
            return redirect('/dashboard/' . Auth::user()->role);
        }

        $controlNumbers = StudentControlNumber::with(['student.room.block', 'student.bed'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('control-numbers.index', compact('controlNumbers'));
    }

    /**
     * Display control number details with transactions
     */
    public function show($id)
    {
        if (Auth::user()->role !== 'owner') {
            return redirect('/dashboard/' . Auth::user()->role);
        }

        $controlNumber = StudentControlNumber::with(['student.room.block', 'student.bed'])
            ->findOrFail($id);

        // Get all payments made through this control number
        // Note: We'll need to link payments to control numbers later
        // For now, get payments for the student
        $payments = Payment::where('student_id', $controlNumber->student_id)
            ->where('status', 'completed')
            ->orderBy('payment_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('control-numbers.show', compact('controlNumber', 'payments'));
    }

    /**
     * Generate hash for name lookup request
     */
    public function generateHash(Request $request)
    {
        if (Auth::user()->role !== 'owner') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $controlNumber = $request->input('control_number');
        
        if (!$controlNumber) {
            return response()->json([
                'success' => false,
                'message' => 'Control number is required'
            ], 400);
        }

        // Verify control number exists
        $controlNumberRecord = StudentControlNumber::where('control_number', $controlNumber)
            ->where('is_active', true)
            ->with('student')
            ->first();

        if (!$controlNumberRecord) {
            return response()->json([
                'success' => false,
                'message' => 'Control number not found'
            ], 404);
        }

        try {
            $secretKey = config('services.azampay.secret_key');

            $currency = $request->input('currency', 'TZS');
            $language = $request->input('language', 'en');
            $country = $request->input('country', 'Tanzania');
            $billType = $request->input('bill_type', 'Hostel');
            $timestamp = $request->input('timestamp', now()->toIso8601String());
            $additionalProperties = $request->input('additional_properties', []);

            // Build Data object for Name Lookup API
            $data = [
                'BillIdentifier' => $controlNumber,
                'Currency' => $currency,
                'Language' => $language,
                'Country' => $country,
                'TimeStamp' => $timestamp,
                'AdditionalProperties' => $additionalProperties,
                'BillType' => $billType
            ];

            // Sort keys to ensure consistent order
            ksort($data);
            if (isset($data['AdditionalProperties']) && is_array($data['AdditionalProperties'])) {
                ksort($data['AdditionalProperties']);
            }

            // Convert to minified JSON (no spaces, no escaped slashes)
            $minifiedJson = json_encode($data, JSON_UNESCAPED_SLASHES);

            // Generate hash using HMAC-SHA256
            $hash = hash_hmac('sha256', $minifiedJson, $secretKey);

            // Build full request body for Postman
            $requestBody = [
                'Data' => $data,
                'Hash' => $hash
            ];

            return response()->json([
                'success' => true,
                'control_number' => $controlNumber,
                'student_name' => $controlNumberRecord->student->full_name ?? 'Unknown',
                'remaining_balance' => $controlNumberRecord->remaining_balance,
                'hash' => $hash,
                'minified_json' => $minifiedJson,
                'request_body' => $requestBody,
                'postman_url' => url('api/merchant/name-lookup'),
                'postman_method' => 'POST',
                'postman_headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error generating hash: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error generating hash: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate hash for payment request
     */
    public function generatePaymentHash(Request $request)
    {
        if (Auth::user()->role !== 'owner') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $controlNumber = $request->input('control_number');
        $amount = $request->input('amount');
        
        if (!$controlNumber || !$amount) {
            return response()->json([
                'success' => false,
                'message' => 'Control number and amount are required'
            ], 400);
        }

        // Verify control number exists
        $controlNumberRecord = StudentControlNumber::where('control_number', $controlNumber)
            ->where('is_active', true)
            ->with('student')
            ->first();

        if (!$controlNumberRecord) {
            return response()->json([
                'success' => false,
                'message' => 'Control number not found'
            ], 404);
        }

        try {
            $secretKey = config('services.azampay.secret_key');

            $fspReferenceId = $request->input('fsp_reference_id', 'fsp' . time());
            $pgReferenceId = $request->input('pg_reference_id', 'pg' . time());
            $currency = $request->input('currency', 'TZS');
            $country = $request->input('country', 'Tanzania');
            $billType = $request->input('bill_type', 'Hostel');
            $timestamp = $request->input('timestamp', now()->toIso8601String());
            $additionalProperties = $request->input('additional_properties', []);
            $paymentDesc = $request->input('payment_desc', 'Hostel Rent Payment');
            $fspCode = $request->input('fsp_code', 'FSP001');

            // Build Data object for Payment API
            $data = [
                'AdditionalProperties' => $additionalProperties,
                'Amount' => (float)$amount,
                'BillIdentifier' => $controlNumber,
                'BillType' => $billType,
                'Country' => $country,
                'FspCode' => $fspCode,
                'FspReferenceId' => $fspReferenceId,
                'PaymentDesc' => $paymentDesc,
                'PgReferenceId' => $pgReferenceId,
                'TimeStamp' => $timestamp
            ];

            // Sort keys to ensure consistent order
            ksort($data);
            if (isset($data['AdditionalProperties']) && is_array($data['AdditionalProperties'])) {
                ksort($data['AdditionalProperties']);
            }

            // Convert to minified JSON
            $minifiedJson = json_encode($data, JSON_UNESCAPED_SLASHES);

            // Generate hash
            $hash = hash_hmac('sha256', $minifiedJson, $secretKey);

            // Build full request body for Postman
            $requestBody = [
                'Data' => $data,
                'Hash' => $hash
            ];

            return response()->json([
                'success' => true,
                'control_number' => $controlNumber,
                'student_name' => $controlNumberRecord->student->full_name ?? 'Unknown',
                'amount' => (float)$amount,
                'remaining_balance' => $controlNumberRecord->remaining_balance,
                'hash' => $hash,
                'minified_json' => $minifiedJson,
                'request_body' => $requestBody,
                'postman_url' => url('api/merchant/payment'),
                'postman_method' => 'POST',
                'postman_headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error generating payment hash: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error generating hash: ' . $e->getMessage()
            ], 500);
        }
    }
}
