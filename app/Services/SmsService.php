<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class SmsService
{
    private $username = 'emcatechn';
    private $password = 'Emca@#12'; // Password with # symbol, will be URL encoded
    private $from = 'PangishaLnk';
    private $baseUrl = 'https://messaging-service.co.tz/link/sms/v1/text/single';

    /**
     * Generate unique control number starting with 3345
     */
    public function generateControlNumber()
    {
        // Generate unique control number starting with 3345
        // Format: 3345 + 5 digits (e.g., 334500001, 334500002)
        $lastControlNumber = \App\Models\StudentControlNumber::where('control_number', 'like', '3345%')
            ->orderBy('control_number', 'desc')
            ->first();
        
        if ($lastControlNumber) {
            // Extract last 5 digits and increment
            $lastDigits = (int) substr($lastControlNumber->control_number, 4);
            $newDigits = str_pad($lastDigits + 1, 5, '0', STR_PAD_LEFT);
        } else {
            // First control number
            $newDigits = '00001';
        }
        
        return '3345' . $newDigits;
    }

    /**
     * Send SMS with control number (sendbox mode)
     */
    public function sendSms($phoneNumber, $message, $controlNumber = null)
    {
        try {
            // Generate control number if not provided
            if (!$controlNumber) {
                $controlNumber = $this->generateControlNumber();
            }

            // Clean phone number - API requires phone number to start with 255 (not +255)
            // Remove all non-numeric characters including + sign
            $phoneNumber = preg_replace('/[^0-9]/', '', $phoneNumber);
            
            // Ensure it starts with country code 255 (not +255)
            if (str_starts_with($phoneNumber, '0')) {
                // If it starts with 0, replace with 255
                $phoneNumber = '255' . substr($phoneNumber, 1);
            } elseif (!str_starts_with($phoneNumber, '255')) {
                // If it doesn't start with 255, add it
                $phoneNumber = '255' . $phoneNumber;
            }
            
            // Final validation: ensure it starts with 255 (no + sign)
            if (!str_starts_with($phoneNumber, '255')) {
                $phoneNumber = '255' . ltrim($phoneNumber, '255');
            }

            $text = urlencode($message);

            // Build URL with control number for sendbox mode
            // Control number is used for tracking SMS delivery
            // Format matches the provided API: https://messaging-service.co.tz/link/sms/v1/text/single?username=...&password=...&from=...&to=...&text=...
            $url = $this->baseUrl . '?username=' . $this->username . 
                   '&password=' . urlencode($this->password) . 
                   '&from=' . $this->from . 
                   '&to=' . $phoneNumber . 
                   '&text=' . $text;

            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0, // No timeout as per API documentation
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
            ));

            $response = curl_exec($curl);
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $error = curl_error($curl);

            curl_close($curl);

            if ($error) {
                Log::error('SMS Error: ' . $error);
                return [
                    'success' => false,
                    'message' => 'SMS sending failed: ' . $error,
                    'control_number' => $controlNumber
                ];
            }

            Log::info('SMS Response', [
                'phone' => $phoneNumber,
                'control_number' => $controlNumber,
                'http_code' => $httpCode,
                'response' => $response
            ]);

            return [
                'success' => $httpCode == 200,
                'message' => $response,
                'http_code' => $httpCode,
                'control_number' => $controlNumber
            ];

        } catch (\Exception $e) {
            Log::error('SMS Exception: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'SMS sending failed: ' . $e->getMessage(),
                'control_number' => $controlNumber ?? null
            ];
        }
    }
}

