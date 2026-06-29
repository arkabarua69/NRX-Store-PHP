<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class PusherBeamsService
{
    protected string $instanceId;
    protected string $secretKey;
    protected string $endpoint;

    public function __construct()
    {
        $this->instanceId = config('services.pusher_beams.instance_id', env('PUSHER_BEAMS_INSTANCE_ID'));
        $this->secretKey = config('services.pusher_beams.secret_key', env('PUSHER_BEAMS_SECRET_KEY'));
        $this->endpoint = "https://{$this->instanceId}.pushnotifications.pusher.com/publish_api/v1/instances/{$this->instanceId}/publishes";
    }

    public function sendToInterests(array $interests, string $title, string $body, ?array $data = null): bool
    {
        $payload = [
            'interests' => $interests,
            'web' => [
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                ],
            ],
        ];

        if ($data) {
            $payload['web']['data'] = $data;
        }

        return $this->publish($payload);
    }

    public function sendToAll(string $title, string $body, ?array $data = null): bool
    {
        return $this->sendToInterests(['orders'], $title, $body, $data);
    }

    public function sendOrderNotification(string $title, string $body, int $orderId): bool
    {
        return $this->sendToInterests(
            ['orders'],
            $title,
            $body,
            ['order_id' => $orderId, 'url' => "/order/{$orderId}"]
        );
    }

    protected function publish(array $payload): bool
    {
        $ch = curl_init($this->endpoint);

        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->secretKey,
            ],
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
        ]);

        $response = curl_exec($ch);
        $curlError = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($curlError) {
            Log::error('Pusher Beams request failed', ['curl_error' => $curlError]);
            return false;
        }

        if ($httpCode !== 200) {
            Log::warning('Pusher Beams returned non-200 response', [
                'http_code' => $httpCode,
                'response' => $response,
            ]);
        }

        return $httpCode === 200;
    }
}
