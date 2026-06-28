<?php

namespace App\Services\Gateway\bkash;

use Exception;
use Illuminate\Support\Facades\Http;

class BkashApi
{
    private $baseUrl;
    private $appKey;
    private $appSecret;
    private $username;
    private $password;
    private $token;

    public function __construct()
    {
        $this->baseUrl = config('services.bkash.base_url', 'https://tokenized.sandbox.bka.sh/v1.2.0-beta');
        $this->appKey = config('services.bkash.app_key');
        $this->appSecret = config('services.bkash.app_secret');
        $this->username = config('services.bkash.username');
        $this->password = config('services.bkash.password');
    }

    public function grantToken(): string
    {
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'username' => $this->username,
            'password' => $this->password,
        ])->post($this->baseUrl . '/tokenized/checkout/token/grant', [
            'app_key' => $this->appKey,
            'app_secret' => $this->appSecret,
        ]);

        $data = $response->json();

        if (!isset($data['id_token'])) {
            throw new Exception($data['message'] ?? 'bKash token grant failed');
        }

        $this->token = $data['id_token'];
        return $this->token;
    }

    public function createPayment($amount, $merchantInvoiceNumber, $callbackUrl)
    {
        if (!$this->token) {
            $this->grantToken();
        }

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => $this->token,
            'X-APP-Key' => $this->appKey,
        ])->post($this->baseUrl . '/tokenized/checkout/create', [
            'mode' => '0011',
            'payerReference' => '01',
            'callbackURL' => $callbackUrl,
            'amount' => (string) $amount,
            'currency' => 'BDT',
            'intent' => 'sale',
            'merchantInvoiceNumber' => $merchantInvoiceNumber,
        ]);

        $data = $response->json();

        if (!isset($data['bkashURL'])) {
            throw new Exception($data['message'] ?? 'bKash payment creation failed');
        }

        return $data;
    }

    public function executePayment($paymentId)
    {
        if (!$this->token) {
            $this->grantToken();
        }

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => $this->token,
            'X-APP-Key' => $this->appKey,
        ])->post($this->baseUrl . '/tokenized/checkout/execute', [
            'paymentID' => $paymentId,
        ]);

        return $response->json();
    }

    public function queryPayment($paymentId)
    {
        if (!$this->token) {
            $this->grantToken();
        }

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => $this->token,
            'X-APP-Key' => $this->appKey,
        ])->post($this->baseUrl . '/tokenized/checkout/payment/status', [
            'paymentID' => $paymentId,
        ]);

        return $response->json();
    }
}
