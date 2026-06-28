<?php

namespace App\Services\Gateway\stripe;

use Exception;
use Illuminate\Support\Facades\Http;

class StripeApi
{
    private $secretKey;
    private $baseUrl = 'https://api.stripe.com/v1';

    public function __construct()
    {
        $this->secretKey = config('services.stripe.secret');
    }

    public function createCheckoutSession($amount, $currency, $successUrl, $cancelUrl, $metadata = [])
    {
        $response = Http::withBasicAuth($this->secretKey, '')
            ->asForm()
            ->post($this->baseUrl . '/checkout/sessions', [
                'mode' => 'payment',
                'payment_method_types[]' => 'card',
                'line_items[0][price_data][currency]' => strtolower($currency),
                'line_items[0][price_data][product_data][name]' => $metadata['product_name'] ?? 'Payment',
                'line_items[0][price_data][unit_amount]' => (int) ($amount * 100),
                'line_items[0][quantity]' => 1,
                'success_url' => $successUrl,
                'cancel_url' => $cancelUrl,
                'metadata' => $metadata,
            ]);

        $data = $response->json();

        if (!isset($data['url'])) {
            throw new Exception($data['error']['message'] ?? 'Stripe session creation failed');
        }

        return $data;
    }

    public function retrieveSession($sessionId)
    {
        $response = Http::withBasicAuth($this->secretKey, '')
            ->get($this->baseUrl . '/checkout/sessions/' . $sessionId);

        return $response->json();
    }

    public function constructEvent($payload, $sigHeader, $endpointSecret)
    {
        $computedSignature = hash_hmac('sha256', $payload, $endpointSecret);
        $expected = explode(',', $sigHeader);
        foreach ($expected as $part) {
            if (str_starts_with($part, 'v1=')) {
                $expectedSig = substr($part, 3);
                if (hash_equals($expectedSig, $computedSignature)) {
                    return json_decode($payload, true);
                }
            }
        }
        throw new Exception('Invalid signature');
    }
}
