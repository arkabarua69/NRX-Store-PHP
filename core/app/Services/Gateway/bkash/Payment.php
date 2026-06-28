<?php

namespace App\Services\Gateway\bkash;

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
            $bkash = new BkashApi();
            $callbackUrl = route('user.deposit.ipn', [$deposit->track_id, $gateway]);
            $response = $bkash->createPayment($deposit->amount, $deposit->track_id, $callbackUrl);

            return ['redirect_url' => $response['bkashURL']];
        } catch (Exception $e) {
            throw new Exception("bKash Error: " . $e->getMessage());
        }
    }

    public static function depositIpn(Request $request, Deposit $deposit, $gateway): array | Exception
    {
        try {
            $bkash = new BkashApi();

            if ($request->status === 'success' && $request->paymentID) {
                $result = $bkash->executePayment($request->paymentID);

                if (isset($result['transactionStatus']) && $result['transactionStatus'] === 'Completed') {
                    $depositService = new DepositService();
                    $depositService->completeDeposit($deposit, 'bkash', $result['trxID']);
                } elseif (isset($result['statusCode']) && $result['statusCode'] === '0000') {
                    $depositService = new DepositService();
                    $depositService->completeDeposit($deposit, 'bkash', $result['trxID']);
                } else {
                    $status = $bkash->queryPayment($request->paymentID);
                    if (isset($status['transactionStatus']) && $status['transactionStatus'] === 'Completed') {
                        $depositService = new DepositService();
                        $depositService->completeDeposit($deposit, 'bkash', $status['trxID']);
                    } else {
                        throw new Exception('Payment not completed');
                    }
                }
            } else {
                throw new Exception('Payment was cancelled or failed');
            }
        } catch (Exception $e) {
            throw new Exception("bKash Verification Error: " . $e->getMessage());
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
            $bkash = new BkashApi();
            $callbackUrl = route('user.order.ipn', [$order->track_id, $gateway]);
            $response = $bkash->createPayment($order->amount, $order->track_id, $callbackUrl);

            return ['redirect_url' => $response['bkashURL']];
        } catch (Exception $e) {
            throw new Exception("bKash Error: " . $e->getMessage());
        }
    }

    public static function orderIpn(Request $request, Order $order, $gateway): array | Exception
    {
        try {
            $bkash = new BkashApi();

            if ($request->status === 'success' && $request->paymentID) {
                $result = $bkash->executePayment($request->paymentID);

                $trxId = $result['trxID'] ?? null;

                if (!$trxId && isset($result['statusCode']) && $result['statusCode'] === '0000') {
                    $status = $bkash->queryPayment($request->paymentID);
                    $trxId = $status['trxID'] ?? null;
                }

                if ($trxId) {
                    $orderService = new OrderService();
                    $orderService->completeOrder($order, 'bkash', $trxId);
                } else {
                    throw new Exception('Payment not completed');
                }
            } else {
                throw new Exception('Payment was cancelled or failed');
            }
        } catch (Exception $e) {
            throw new Exception("bKash Verification Error: " . $e->getMessage());
        }

        return [
            'status' => 'success',
            'message' => __('Order Successful.'),
            'redirect' => orderIpnRedirectUrl($order)
        ];
    }
}
