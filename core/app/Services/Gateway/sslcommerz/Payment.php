<?php

namespace App\Services\Gateway\sslcommerz;

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
        $storeId = config('services.sslcommerz.store_id');
        $storePassword = config('services.sslcommerz.store_password');
        $sandbox = config('services.sslcommerz.sandbox', true);

        $postData = [
            'store_id' => $storeId,
            'store_passwd' => $storePassword,
            'total_amount' => $deposit->amount,
            'currency' => gs()->base_currency ?? 'BDT',
            'tran_id' => $deposit->track_id,
            'success_url' => depositRedirectUrl($deposit, $gateway),
            'fail_url' => depositCancelUrl(),
            'cancel_url' => depositCancelUrl(),
            'cus_name' => $deposit->user->name ?? 'Customer',
            'cus_email' => $deposit->user->email ?? 'customer@example.com',
            'cus_add1' => 'N/A',
            'cus_city' => 'N/A',
            'cus_country' => 'Bangladesh',
            'cus_phone' => $deposit->user->phone ?? '01700000000',
            'shipping_method' => 'NO',
            'product_name' => 'Wallet Deposit',
            'product_category' => 'Deposit',
            'product_profile' => 'general',
        ];

        $sandboxUrl = 'https://sandbox.sslcommerz.com/gwprocess/v4/api.php';
        $liveUrl = 'https://secure.sslcommerz.com/gwprocess/v4/api.php';
        $apiUrl = $sandbox ? $sandboxUrl : $liveUrl;

        try {
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $apiUrl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => http_build_query($postData),
                CURLOPT_TIMEOUT => 30,
            ]);
            $response = curl_exec($ch);
            $error = curl_error($ch);
            curl_close($ch);

            if ($error) {
                throw new Exception("cURL Error: $error");
            }

            $data = json_decode($response, true);

            if (!isset($data['GatewayPageURL'])) {
                throw new Exception($data['failedreason'] ?? 'SSLCommerz initialization failed');
            }

            return ['redirect_url' => $data['GatewayPageURL']];
        } catch (Exception $e) {
            throw new Exception("SSLCommerz Error: " . $e->getMessage());
        }
    }

    public static function depositIpn(Request $request, Deposit $deposit, $gateway): array | Exception
    {
        $storeId = config('services.sslcommerz.store_id');
        $storePassword = config('services.sslcommerz.store_password');

        $sandbox = config('services.sslcommerz.sandbox', true);
        $validationUrl = $sandbox
            ? 'https://sandbox.sslcommerz.com/validator/api/validationserverAPI.php'
            : 'https://secure.sslcommerz.com/validator/api/validationserverAPI.php';

        $validationUrl .= '?val_id=' . $request->val_id . '&store_id=' . $storeId . '&store_passwd=' . $storePassword . '&v=1&format=json';

        try {
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $validationUrl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 30,
            ]);
            $response = curl_exec($ch);
            $error = curl_error($ch);
            curl_close($ch);

            if ($error) {
                throw new Exception("Validation cURL Error: $error");
            }

            $data = json_decode($response, true);

            if ($data['status'] === 'VALID' && $data['tran_id'] === $deposit->track_id) {
                $depositService = new DepositService();
                $depositService->completeDeposit($deposit, 'sslcommerz', $data['tran_id']);
            } else {
                throw new Exception('Payment validation failed');
            }
        } catch (Exception $e) {
            throw new Exception("SSLCommerz Verification Error: " . $e->getMessage());
        }

        return [
            'status' => 'success',
            'message' => __('Add Fund Successful.'),
            'redirect' => depositIpnRedirectUrl()
        ];
    }

    public static function prepareOrderData(Order $order, $gateway): array | Exception
    {
        $storeId = config('services.sslcommerz.store_id');
        $storePassword = config('services.sslcommerz.store_password');
        $sandbox = config('services.sslcommerz.sandbox', true);

        $postData = [
            'store_id' => $storeId,
            'store_passwd' => $storePassword,
            'total_amount' => $order->amount,
            'currency' => gs()->base_currency ?? 'BDT',
            'tran_id' => $order->track_id,
            'success_url' => orderRedirectUrl($order, $gateway),
            'fail_url' => orderCancelUrl($order),
            'cancel_url' => orderCancelUrl($order),
            'cus_name' => $order->user->name ?? 'Customer',
            'cus_email' => $order->user->email ?? 'customer@example.com',
            'cus_add1' => 'N/A',
            'cus_city' => 'N/A',
            'cus_country' => 'Bangladesh',
            'cus_phone' => $order->user->phone ?? '01700000000',
            'shipping_method' => 'NO',
            'product_name' => $order->product->title,
            'product_category' => $order->product->type,
            'product_profile' => 'general',
        ];

        $sandboxUrl = 'https://sandbox.sslcommerz.com/gwprocess/v4/api.php';
        $liveUrl = 'https://secure.sslcommerz.com/gwprocess/v4/api.php';
        $apiUrl = $sandbox ? $sandboxUrl : $liveUrl;

        try {
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $apiUrl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => http_build_query($postData),
                CURLOPT_TIMEOUT => 30,
            ]);
            $response = curl_exec($ch);
            $error = curl_error($ch);
            curl_close($ch);

            if ($error) {
                throw new Exception("cURL Error: $error");
            }

            $data = json_decode($response, true);

            if (!isset($data['GatewayPageURL'])) {
                throw new Exception($data['failedreason'] ?? 'SSLCommerz initialization failed');
            }

            return ['redirect_url' => $data['GatewayPageURL']];
        } catch (Exception $e) {
            throw new Exception("SSLCommerz Error: " . $e->getMessage());
        }
    }

    public static function orderIpn(Request $request, Order $order, $gateway): array | Exception
    {
        $storeId = config('services.sslcommerz.store_id');
        $storePassword = config('services.sslcommerz.store_password');

        $sandbox = config('services.sslcommerz.sandbox', true);
        $validationUrl = $sandbox
            ? 'https://sandbox.sslcommerz.com/validator/api/validationserverAPI.php'
            : 'https://secure.sslcommerz.com/validator/api/validationserverAPI.php';

        $validationUrl .= '?val_id=' . $request->val_id . '&store_id=' . $storeId . '&store_passwd=' . $storePassword . '&v=1&format=json';

        try {
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $validationUrl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 30,
            ]);
            $response = curl_exec($ch);
            $error = curl_error($ch);
            curl_close($ch);

            if ($error) {
                throw new Exception("Validation cURL Error: $error");
            }

            $data = json_decode($response, true);

            if ($data['status'] === 'VALID' && $data['tran_id'] === $order->track_id) {
                $orderService = new OrderService();
                $orderService->completeOrder($order, 'sslcommerz', $data['tran_id']);
            } else {
                throw new Exception('Payment validation failed');
            }
        } catch (Exception $e) {
            throw new Exception("SSLCommerz Verification Error: " . $e->getMessage());
        }

        return [
            'status' => 'success',
            'message' => __('Order Successful.'),
            'redirect' => orderIpnRedirectUrl($order)
        ];
    }
}
