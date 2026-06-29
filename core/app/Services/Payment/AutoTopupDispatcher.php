<?php

namespace App\Services\Payment;

use App\Constants\OrderStatus;
use App\Constants\Status;
use App\Helpers\ActivityLogger;
use App\Models\AutoVoucher;
use App\Models\Order;
use Exception;
use Illuminate\Support\Facades\Log;

class AutoTopupDispatcher
{
    public static function dispatchIfEligible(Order $order): void
    {
        try {
            $autoVoucher = AutoVoucher::where('status', Status::AVAILABLE)
                ->where('variation_id', $order->variation_id)
                ->first();

            if (! self::isEligible($order, $autoVoucher)) {
                return;
            }

            $order->status = OrderStatus::AUTOPROCESSING;
            $order->voucher_code = $autoVoucher->code;
            $order->save();

            $autoVoucher->order_id = $order->id;
            $autoVoucher->status = Status::SOLD;
            $autoVoucher->save();

            ActivityLogger::log(
                "Order #{$order->id} sent for auto-processing via {$order->variation->provider}",
                $order,
                logName: 'order',
                event: 'auto_processing',
                properties: ['provider' => $order->variation->provider]
            );
        } catch (Exception $e) {
            Log::warning('Auto-topup dispatch failed', [
                'order' => $order->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private static function isEligible(Order $order, ?AutoVoucher $autoVoucher): bool
    {
        return $order->product->isTopup()
            && $order->variation->isAutomatic()
            && $autoVoucher
            && gs()->enable_auto_topup
            && gs()->free_fire_server_url
            && gs()->free_fire_server_api_key;
    }
}
