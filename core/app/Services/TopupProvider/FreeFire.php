<?php

namespace App\Services\TopupProvider;

use App\Constants\OrderStatus;
use App\Constants\Status;
use App\Jobs\VerifyOrderStatus;
use App\Models\AutoVoucher;
use App\Services\TopupProvider\TopupProviderService;
use Exception;
use Illuminate\Support\Facades\Http;

class FreeFire extends TopupProviderService
{
    public function placeOrder(AutoVoucher $autoVoucher)
    {
        try {
            $response = Http::withHeaders(
                [
                    'Content-Type'  => 'application/json',
                    'Accept'        => 'application/json',
                    'RA-SECRET-KEY' => $this->apiKey,
                ]
            )->post($this->baseUrl . "/topup", [
                'playerId'    => $this->order->account_info['player_id'],
                'denom'       => (string) $this->order->variation->provider_product_id,
                'type'        => (string) $this->getCodeType($autoVoucher->code),
                'voucherCode' => $this->getCode($autoVoucher->code),
                'webhook'     => route('auto.topup.webhook'),
            ]);

            if ($response->successful() && $response->json('uid')) {
                $this->order->provider_data = [
                    'track_id' => $response->json('uid'),
                    'message'  => $response->json('message'),
                ];
                $this->order->save();

                VerifyOrderStatus::dispatch($this->order, $autoVoucher)->onQueue('order')->delay(now()->addMinutes(1));
            } else {
                $this->order->provider_data = [
                    'message' => $response->json('message'),
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

    public function verify(AutoVoucher $autoVoucher)
    {
        try {
            $track_id = $this->order->provider_data['track_id'];
            $url = $this->baseUrl . "/transactions/{$track_id}";
            $response = Http::get($url);

            if ($response->successful() && $response->json('status') === 'success') {
                $this->order->provider_data = [
                    'message' => $response->json('message'),
                ];
                $this->order->status = OrderStatus::COMPLETED;
                $this->order->save();
            } else {
                $this->order->provider_data = [
                    'message' => $response->json('message'),
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
                'message' => $response->json('message'),
            ];
            $this->order->voucher_code = null;
            $this->order->status = OrderStatus::PROCESSING;
            $this->order->save();

            $autoVoucher->order_id = null;
            $autoVoucher->status = Status::AVAILABLE;
            $autoVoucher->save();
        }
    }

    private function getCodeType($code)
    {
         if (is_array($code)) {
        $code = $code[0]; 
    }
        $prefix = substr($code, 0, 4);

        if ($prefix === 'UPBD') {
            return 2;
        } elseif ($prefix === 'BDMB') {
            return 1;
        } else {
            return 0;
        }
    }

    private function getCode($code)
    {
         if (is_array($code)) {
        $code = $code[0]; 
    }
        return substr($code, 5);
    }
}
