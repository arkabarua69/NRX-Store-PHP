<?php

namespace App\Services\Traits;

use App\Services\Gateway\GatewayInterface;

trait ResolvesGateway
{
    private static array $allowedGateways = ['uddoktapay', 'stripe', 'sslcommerz', 'bkash'];

    private function resolveGateway(string $gateway): GatewayInterface
    {
        if (! in_array($gateway, self::$allowedGateways)) {
            throw new \Exception('Invalid payment gateway.');
        }

        $gatewayClass = 'App\\Services\\Gateway\\'.$gateway.'\\Payment';
        $gatewayObj = app($gatewayClass);

        if (! ($gatewayObj instanceof GatewayInterface)) {
            throw new \Exception('The Payment gateway must implement GatewayInterface.');
        }

        return $gatewayObj;
    }
}
