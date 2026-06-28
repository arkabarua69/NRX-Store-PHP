<?php

namespace App\Services\Gateway\stripe;

use App\Models\Deposit;
use App\Models\Order;
use App\Services\DepositService;
use App\Services\Gateway\GatewayInterface;
use App\Services\OrderService;
use Exception;
use Illuminate\Http\Request;

class Payment implements GatewayInterface
{
    public static function prepareDepositData(Deposit $deposit, $gateway): array | Exception
    {
        try {
            $stripe = new StripeApi();
            $currency = gs()->base_currency ?? 'USD';

            $response = $stripe->createCheckoutSession(
                $deposit->amount,
                $currency,
                depositRedirectUrl($deposit, $gateway),
                depositCancelUrl(),
                [
                    'type' => 'deposit',
                    'track_id' => $deposit->track_id,
                    'user_id' => (string) $deposit->user_id,
                ]
            );

            return ['redirect_url' => $response['url']];
        } catch (Exception $e) {
            throw new Exception("Stripe Error: " . $e->getMessage());
        }
    }

    public static function depositIpn(Request $request, Deposit $deposit, $gateway): array | Exception
    {
        try {
            $stripe = new StripeApi();

            $sessionId = $request->session_id;
            if (!$sessionId) {
                throw new Exception('No session ID provided');
            }

            $session = $stripe->retrieveSession($sessionId);

            if ($session['payment_status'] === 'paid') {
                $depositService = new DepositService();
                $depositService->completeDeposit($deposit, 'stripe', $session['id']);
            } else {
                throw new Exception('Payment not completed');
            }
        } catch (Exception $e) {
            throw new Exception("Stripe Verification Error: " . $e->getMessage());
        }

        return [
            'status' => 'success',
            'message' => __('Add Fund Successful.'),
            'redirect' => depositIpnRedirectUrl()
        ];
    }

    public static function prepareOrderData(Order $order, $gateway): array | Exception
    {
        try {
            $stripe = new StripeApi();
            $currency = gs()->base_currency ?? 'USD';

            $response = $stripe->createCheckoutSession(
                $order->amount,
                $currency,
                orderRedirectUrl($order, $gateway),
                orderCancelUrl($order),
                [
                    'type' => 'order',
                    'track_id' => $order->track_id,
                    'order_id' => (string) $order->id,
                ]
            );

            return ['redirect_url' => $response['url']];
        } catch (Exception $e) {
            throw new Exception("Stripe Error: " . $e->getMessage());
        }
    }

    public static function orderIpn(Request $request, Order $order, $gateway): array | Exception
    {
        try {
            $stripe = new StripeApi();

            $sessionId = $request->session_id;
            if (!$sessionId) {
                throw new Exception('No session ID provided');
            }

            $session = $stripe->retrieveSession($sessionId);

            if ($session['payment_status'] === 'paid') {
                $orderService = new OrderService();
                $orderService->completeOrder($order, 'stripe', $session['id']);
            } else {
                throw new Exception('Payment not completed');
            }
        } catch (Exception $e) {
            throw new Exception("Stripe Verification Error: " . $e->getMessage());
        }

        return [
            'status' => 'success',
            'message' => __('Order Successful.'),
            'redirect' => orderIpnRedirectUrl($order)
        ];
    }
}
