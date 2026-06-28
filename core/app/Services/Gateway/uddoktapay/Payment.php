<?php

namespace App\Services\Gateway\uddoktapay;

use Exception;
use App\Models\Order;
use App\Models\Deposit;
use Illuminate\Http\Request;
use App\Services\OrderService;
use App\Services\DepositService;
use App\Services\Gateway\GatewayInterface;
use App\Services\Gateway\uddoktapay\UddoktaPay;

class Payment implements GatewayInterface
{
    public static function prepareDepositData(Deposit $deposit, $gateway): array | Exception
    {
        $apiKey = gs()->uddoktapay_api_key;
        $apiBaseURL = gs()->uddoktapay_api_url;
        $uddoktaPay = new UddoktaPay($apiKey, $apiBaseURL);

        $requestData = [
            'full_name'    => $deposit->user->name ?? 'Test User',
            'email'        => $deposit->user->email ?? 'test@test.com',
            'amount'       => $deposit->amount,
            'metadata'     => [
                'track_id' => $deposit->track_id,
            ],
            'redirect_url' => depositRedirectUrl($deposit, $gateway),
            'return_type'  => 'GET',
            'cancel_url'   => depositCancelUrl(),
            'webhook_url'  => depositRedirectUrl($deposit, $gateway),
        ];

        try {
            $paymentUrl = $uddoktaPay->initPayment($requestData);
            $response = [
                'redirect_url' => $paymentUrl,
            ];
        } catch (Exception $e) {
            throw new Exception("Initialization Error: " . $e->getMessage());
        }
        return $response;
    }

    public static function depositIpn(Request $request, Deposit $deposit, $gateway): array | Exception
    {
        $apiKey = gs()->uddoktapay_api_key;
        $apiBaseURL = gs()->uddoktapay_api_url;
        $uddoktaPay = new UddoktaPay($apiKey, $apiBaseURL);

        try {
            $response = $uddoktaPay->verifyPayment($request->invoice_id);
        } catch (Exception $e) {
            throw new Exception("Verification Error: " . $e->getMessage());
        }

        try {
            if ($response['status'] === 'COMPLETED') {
                $depositService = new DepositService();
                $depositService->completeDeposit($deposit, $response['payment_method'], $response['transaction_id']);
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }

        $data = [
            'status'   => 'success',
            'message'  => __('Add Fund Successful.'),
            'redirect' => depositIpnRedirectUrl()
        ];
        return $data;
    }

    public static function prepareOrderData(Order $order, $gateway): array | Exception
    {
        $apiKey = gs()->uddoktapay_api_key;
        $apiBaseURL = gs()->uddoktapay_api_url;
        $uddoktaPay = new UddoktaPay($apiKey, $apiBaseURL);

        $requestData = [
            'full_name'    => $order->user->name ?? 'Test User',
            'email'        => $order->user->email ?? 'test@test.com',
            'amount'       => $order->amount,
            'metadata'     => [
                'order_id' => $order->id,
                'track_id' => $order->track_id,
            ],
            'redirect_url' => orderRedirectUrl($order, $gateway),
            'return_type'  => 'GET',
            'cancel_url'   => orderCancelUrl($order),
            'webhook_url'  => orderRedirectUrl($order, $gateway),
        ];

        try {
            $paymentUrl = $uddoktaPay->initPayment($requestData);
            $response = [
                'redirect_url' => $paymentUrl,
            ];
        } catch (Exception $e) {
            throw new Exception("Initialization Error: " . $e->getMessage());
        }

        return $response;
    }

    public static function orderIpn(Request $request, Order $order, $gateway): array | Exception
    {
        $apiKey = gs()->uddoktapay_api_key;
        $apiBaseURL = gs()->uddoktapay_api_url;
        $uddoktaPay = new UddoktaPay($apiKey, $apiBaseURL);

        try {
            $response = $uddoktaPay->verifyPayment($request->invoice_id);
        } catch (Exception $e) {
            throw new Exception("Verification Error: " . $e->getMessage());
        }

        try {
            if ($response['status'] === 'COMPLETED') {
                $orderService = new OrderService();
                $orderService->completeOrder($order, $response['payment_method'], $response['transaction_id']);
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }

        $data = [
            'status'   => 'success',
            'message'  => __('Order Successful.'),
            'redirect' => orderIpnRedirectUrl($order)
        ];
        return $data;
    }
}
