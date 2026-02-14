<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaystackService
{
    protected string $secretKey;
    protected string $baseUrl = 'https://api.paystack.co';

    public function __construct()
    {
        $this->secretKey = config('services.paystack.secret_key');
        if (empty($this->secretKey)) {
             Log::warning('Paystack secret key is not set in environment.');
        }
    }

    /**
     * Initialize a transaction.
     */
    public function initializeTransaction(array $data)
    {
        try {
            $response = Http::withToken($this->secretKey)
                ->post("{$this->baseUrl}/transaction/initialize", [
                    'amount' => $data['amount'] * 100, // Paystack expects amount in Kobo/cents
                    'email' => $data['email'],
                    'reference' => $data['reference'],
                    'callback_url' => $data['callback_url'] ?? null,
                    'metadata' => $data['metadata'] ?? [],
                ]);

            if ($response->successful()) {
                return $response->json();
            }

            throw new Exception("Paystack Initialization Error: " . $response->body());
        } catch (Exception $e) {
            Log::error($e->getMessage());
            throw $e;
        }
    }

    /**
     * Verify a transaction.
     */
    public function verifyTransaction(string $reference)
    {
        try {
            $response = Http::withToken($this->secretKey)
                ->get("{$this->baseUrl}/transaction/verify/" . rawurlencode($reference));

            if ($response->successful()) {
                return $response->json();
            }

            throw new Exception("Paystack Verification Error: " . $response->body());
        } catch (Exception $e) {
            Log::error($e->getMessage());
            throw $e;
        }
    }

    /**
     * Get a list of supported banks.
     */
    public function getBanks()
    {
        try {
            $response = Http::withToken($this->secretKey)
                ->get("{$this->baseUrl}/bank");

            if ($response->successful()) {
                return $response->json();
            }

            throw new Exception("Paystack Bank List Error: " . $response->body());
        } catch (Exception $e) {
            Log::error($e->getMessage());
            throw $e;
        }
    }

    /**
     * Create a transfer recipient.
     */
    public function createTransferRecipient(array $data)
    {
        try {
            $response = Http::withToken($this->secretKey)
                ->post("{$this->baseUrl}/transferrecipient", [
                    'type' => 'nuban',
                    'name' => $data['name'],
                    'account_number' => $data['account_number'],
                    'bank_code' => $data['bank_code'],
                    'currency' => 'KES', // or NGN depending on your currency
                ]);

            if ($response->successful()) {
                return $response->json();
            }

            throw new Exception("Paystack Recipient Error: " . $response->body());
        } catch (Exception $e) {
            Log::error($e->getMessage());
            throw $e;
        }
    }

    /**
     * Initiate a transfer/withdrawal.
     */
    public function initiateTransfer(array $data)
    {
        try {
            $response = Http::withToken($this->secretKey)
                ->post("{$this->baseUrl}/transfer", [
                    'source' => 'balance',
                    'amount' => $data['amount'] * 100,
                    'recipient' => $data['recipient_code'],
                    'reason' => $data['reason'] ?? 'Withdrawal',
                ]);

            if ($response->successful()) {
                return $response->json();
            }

            throw new Exception("Paystack Transfer Error: " . $response->body());
        } catch (Exception $e) {
            Log::error($e->getMessage());
            throw $e;
        }
    }

    /**
     * Verify Webhook Signature.
     */
    public function verifyWebhook(string $signature, string $payload): bool
    {
        return $signature === hash_hmac('sha512', $payload, $this->secretKey);
    }
}
