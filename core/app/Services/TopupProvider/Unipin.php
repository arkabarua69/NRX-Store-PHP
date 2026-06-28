<?php

namespace App\Services\TopupProvider;

use App\Constants\OrderStatus;
use App\Constants\Status;
use App\Jobs\VerifyOrderStatus;
use App\Models\AutoVoucher;
use App\Services\TopupProvider\TopupProviderService;
use Exception;
use Illuminate\Support\Facades\Http;

class Unipin extends TopupProviderService
{
    protected $partnerId;
    protected $secretKey;

    public function __construct(public $order)
    {
        parent::__construct($order);
        $this->partnerId = gs()->unipin_partner_id;
        $this->secretKey = gs()->unipin_secret_key;
        $this->baseUrl = gs()->unipin_server_url ?? 'https://api.unipin.com';
    }

    public function placeOrder(AutoVoucher $autoVoucher)
    {
        try {
            $gameCode = $this->order->variation->provider_product_id;
            $denominationId = $this->order->variation->denomination_id;
            $referenceNo = $this->order->track_id;

            // First validate user
            $validationResponse = $this->validateUser($gameCode, $this->order->account_info);

            if (!$validationResponse['status']) {
                throw new Exception('User validation failed: ' . $validationResponse['message']);
            }

            $validationToken = $validationResponse['validation_token'];

            // Create order
            $orderResponse = $this->createOrder($gameCode, $validationToken, $referenceNo, $denominationId);

            if (!$orderResponse['status']) {
                throw new Exception('Order creation failed: ' . $orderResponse['message']);
            }

            $this->order->provider_data = [
                'transaction_number' => $orderResponse['transaction_number'],
                'reference_no' => $orderResponse['reference_no'],
                'amount' => $orderResponse['amount'],
                'currency' => $orderResponse['currency'],
                'item_name' => $orderResponse['item_name'],
            ];
            $this->order->save();

            VerifyOrderStatus::dispatch($this->order, $autoVoucher)->onQueue('order')->delay(now()->addMinutes(1));

        } catch (Exception $e) {
            $this->order->provider_data = [
                'message' => $e->getMessage(),
            ];
            $this->order->voucher_code = null;
            $this->order->status = OrderStatus::PROCESSING;
            $this->order->save();

            $autoVoucher->order_id = null;
            $autoVoucher->status = Status::AVAILABLE;
            $autoVoucher->save();
        }
    }

    public function verify(AutoVoucher $autoVoucher)
    {
        try {
            $referenceNo = $this->order->track_id;
            $response = $this->orderInquiry($referenceNo);

            if ($response['status'] && isset($response['transaction_number'])) {
                $this->order->provider_data = [
                    'transaction_number' => $response['transaction_number'],
                    'reference_no' => $response['reference_no'],
                    'amount' => $response['amount'],
                    'currency' => $response['currency'],
                    'item_name' => $response['item_name'],
                ];
                $this->order->status = OrderStatus::COMPLETED;
                $this->order->save();
            } else {
                $this->order->provider_data = [
                    'message' => $response['message'] ?? 'Order inquiry failed',
                ];
                $this->order->voucher_code = null;
                $this->order->status = OrderStatus::PROCESSING;
                $this->order->save();

                $autoVoucher->order_id = null;
                $autoVoucher->status = Status::AVAILABLE;
                $autoVoucher->save();
            }
        } catch (Exception $e) {
            $this->order->provider_data = [
                'message' => $e->getMessage(),
            ];
            $this->order->voucher_code = null;
            $this->order->status = OrderStatus::PROCESSING;
            $this->order->save();

            $autoVoucher->order_id = null;
            $autoVoucher->status = Status::AVAILABLE;
            $autoVoucher->save();
        }
    }

    private function generateAuthHeader($path)
    {
        $timestamp = time();
        $authString = $this->partnerId . $timestamp . $path;
        $signature = hash_hmac('sha256', $authString, $this->secretKey);

        return [
            'partnerid' => $this->partnerId,
            'timestamp' => $timestamp,
            'path' => $path,
            'auth' => $signature,
        ];
    }

    private function validateUser($gameCode, $fields)
    {
        $path = '/in-game-topup/user/validate';
        $headers = $this->generateAuthHeader($path);

        $response = Http::withHeaders($headers)->post($this->baseUrl . $path, [
            'game_code' => $gameCode,
            'fields' => $fields,
        ]);

        $data = $response->json();

        if ($response->successful() && isset($data['status']) && $data['status'] == 1) {
            return [
                'status' => true,
                'validation_token' => $data['validation_token'] ?? null,
                'username' => $data['username'] ?? null,
                'message' => $data['reason'] ?? 'Success',
            ];
        }

        return [
            'status' => false,
            'message' => $data['error']['message'] ?? $data['reason'] ?? 'Validation failed',
        ];
    }

    private function createOrder($gameCode, $validationToken, $referenceNo, $denominationId)
    {
        $path = '/in-game-topup/order/create';
        $headers = $this->generateAuthHeader($path);

        $response = Http::withHeaders($headers)->post($this->baseUrl . $path, [
            'game_code' => $gameCode,
            'validation_token' => $validationToken,
            'reference_no' => $referenceNo,
            'denomination_id' => $denominationId,
        ]);

        $data = $response->json();

        if ($response->successful() && isset($data['status']) && $data['status'] == 1) {
            return [
                'status' => true,
                'transaction_number' => $data['transaction_number'] ?? null,
                'reference_no' => $data['reference_no'] ?? null,
                'amount' => $data['amount'] ?? null,
                'currency' => $data['currency'] ?? null,
                'item_name' => $data['item_name'] ?? null,
                'message' => $data['reason'] ?? 'Success',
            ];
        }

        return [
            'status' => false,
            'message' => $data['error']['message'] ?? $data['reason'] ?? 'Order creation failed',
        ];
    }

    private function orderInquiry($referenceNo)
    {
        $path = '/in-game-topup/order/inquiry';
        $headers = $this->generateAuthHeader($path);

        $response = Http::withHeaders($headers)->post($this->baseUrl . $path, [
            'reference_no' => $referenceNo,
        ]);

        $data = $response->json();

        if ($response->successful() && isset($data['status']) && $data['status'] == 1) {
            return [
                'status' => true,
                'transaction_number' => $data['transaction_number'] ?? null,
                'reference_no' => $data['reference_no'] ?? null,
                'amount' => $data['amount'] ?? null,
                'currency' => $data['currency'] ?? null,
                'item_name' => $data['item_name'] ?? null,
                'message' => $data['reason'] ?? 'Success',
            ];
        }

        return [
            'status' => false,
            'message' => $data['error']['message'] ?? $data['reason'] ?? 'Order inquiry failed',
        ];
    }

    public function getGameList()
    {
        $path = '/in-game-topup/list';
        $headers = $this->generateAuthHeader($path);

        $response = Http::withHeaders($headers)->post($this->baseUrl . $path);

        return $response->json();
    }

    public function getGameDetail($gameCode)
    {
        $path = '/in-game-topup/detail';
        $headers = $this->generateAuthHeader($path);

        $response = Http::withHeaders($headers)->post($this->baseUrl . $path, [
            'game_code' => $gameCode,
        ]);

        return $response->json();
    }
}
