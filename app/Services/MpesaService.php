<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MpesaService
{
    protected $consumerKey;
    protected $consumerSecret;
    protected $shortCode;
    protected $passkey;
    protected $callbackUrl;
    protected $baseUrl;

    public function __construct()
    {
        $this->consumerKey = config('services.mpesa.consumer_key');
        $this->consumerSecret = config('services.mpesa.consumer_secret');
        $this->shortCode = config('services.mpesa.paybill_number');
        $this->passkey = config('services.mpesa.passkey');
        $this->callbackUrl = config('services.mpesa.callback_url');
        $this->baseUrl = config('services.mpesa.environment') == 'production'
            ? 'https://api.safaricom.co.ke'
            : 'https://sandbox.safaricom.co.ke';

        // Fallback for development if config is missing
        if (empty($this->baseUrl)) {
            $this->baseUrl = 'https://sandbox.safaricom.co.ke';
        }
    }

    /**
     * Generate Access Token
     */
    public function getAccessToken()
    {
        // For development/testing without real credentials, return a mock token
        if (config('app.env') === 'local' && empty($this->consumerKey)) {
            return 'mock-access-token-' . uniqid();
        }

        try {
            $response = Http::withBasicAuth($this->consumerKey, $this->consumerSecret)
                ->get($this->baseUrl . '/oauth/v1/generate?grant_type=client_credentials');

            if ($response->successful()) { // Updated to use successful()
                return $response->json()['access_token'];
            }

            Log::error('Mpesa Access Token Error: ' . $response->body());
            return null;
        } catch (\Exception $e) {
            Log::error('Mpesa Access Token Exception: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Initiate STK Push
     */
    public function stkPush($phoneNumber, $amount, $accountReference = 'SchoolFees', $transactionDesc = 'Fee Payment')
    {
        // Format phone number to 254...
        $phoneNumber = $this->formatPhoneNumber($phoneNumber);

        // Use a mock response for local dev if keys are missing
        if (config('app.env') === 'local' && empty($this->passkey)) {
            Log::info("Simulating STK Push to {$phoneNumber} for KSh {$amount}");
            return [
                'success' => true,
                'CheckoutRequestID' => 'ws_CO_' . uniqid(),
                'ResponseCode' => '0',
                'ResponseDescription' => 'Success. Request accepted for processing',
                'CustomerMessage' => 'Success. Request accepted for processing'
            ];
        }

        $token = $this->getAccessToken();
        if (!$token) {
            return ['success' => false, 'message' => 'Failed to generate access token'];
        }

        $timestamp = date('YmdHis');
        $password = base64_encode($this->shortCode . $this->passkey . $timestamp);

        try {
            $response = Http::withToken($token)
                ->post($this->baseUrl . '/mpesa/stkpush/v1/processrequest', [
                    'BusinessShortCode' => $this->shortCode,
                    'Password' => $password,
                    'Timestamp' => $timestamp,
                    'TransactionType' => 'CustomerPayBillOnline',
                    'Amount' => (int)$amount,
                    'PartyA' => $phoneNumber,
                    'PartyB' => $this->shortCode,
                    'PhoneNumber' => $phoneNumber,
                    'CallBackURL' => $this->callbackUrl,
                    'AccountReference' => $accountReference,
                    'TransactionDesc' => $transactionDesc
                ]);

            Log::info('STK Push Response: ' . $response->body());

            if ($response->successful()) {
                $data = $response->json();
                $data['success'] = true; // Add success flag
                return $data;
            } else {
                return [
                    'success' => false,
                    'message' => $response->json()['errorMessage'] ?? 'STK Push failed'
                ];
            }
        } catch (\Exception $e) {
            Log::error('STK Push Exception: ' . $e->getMessage());
            return ['success' => false, 'message' => 'STK Push error: ' . $e->getMessage()];
        }
    }

    /**
     * Format phone number to 2547XXXXXXXX
     */
    private function formatPhoneNumber($phone)
    {
        $phone = preg_replace('/[^0-9]/', '', $phone); // Remove non-numeric

        if (strlen($phone) == 10 && substr($phone, 0, 1) == '0') {
            return '254' . substr($phone, 1);
        }

        if (strlen($phone) == 9 && substr($phone, 0, 1) == '7') {
            return '254' . $phone;
        }

        return $phone;
    }
}
