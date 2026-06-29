<?php

namespace App\Services\Payment;

use App\Constants\Status;
use App\Models\Order;
use App\Models\Voucher;
use Illuminate\Support\Collection;

class VoucherFulfillment
{
    public static function fetchAvailableVouchers(int $variationId, int $quantity): Collection
    {
        return Voucher::where('status', Status::AVAILABLE)
            ->where('variation_id', $variationId)
            ->limit($quantity)
            ->orderBy('id', 'DESC')
            ->get();
    }

    public static function assignVouchersToOrder(Order $order, Collection $vouchers): void
    {
        $variation = $order->variation;
        $variation->stock -= $vouchers->count();
        $variation->save();

        $voucherCodes = [];
        foreach ($vouchers as $index => $voucher) {
            $voucherCodes[] = is_array($voucher->code) ? implode(',', $voucher->code) : $voucher->code;
            $voucher->status = Status::SOLD;
            $voucher->order_id = $order->id;
            $voucher->save();

            if ($index < $order->quantity - 1) {
                $voucherCodes[] = ',';
            }
        }

        $order['voucher_code'] = implode('', $voucherCodes);
        $order->update();
    }

    public static function decrementStock(Order $order, int $quantity = 1): void
    {
        $variation = $order->variation;
        $variation->stock -= $quantity;
        $variation->save();
    }
}
