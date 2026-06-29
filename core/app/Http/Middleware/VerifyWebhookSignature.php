<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class VerifyWebhookSignature
{
    public function handle(Request $request, Closure $next)
    {
        $signature = $request->header('X-Webhook-Signature');
        $sharedSecret = config('app.webhook_secret');

        if (!$sharedSecret || !$signature) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $payload = $request->getContent();
        $expectedSignature = hash_hmac('sha256', $payload, $sharedSecret);

        if (!hash_equals($expectedSignature, $signature)) {
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        return $next($request);
    }
}
