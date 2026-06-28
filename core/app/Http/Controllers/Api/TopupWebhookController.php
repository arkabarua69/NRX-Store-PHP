<?php

namespace App\Http\Controllers\Api;

use App\Constants\OrderStatus;
use App\Constants\Status;
use App\Models\AutoVoucher;
use App\Models\Order;
use App\Models\Transaction;
use App\Models\User;
use App\Mail\OrderStatusChanged;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class TopupWebhookController
{
    public function handle(Request $request)
    {
        Log::info('Auto-topup webhook received', $request->all());

        $validated = $request->validate([
            'track_id' => 'required|string',
            'status' => 'required|string|in:success,failed,pending',
            'message' => 'nullable|string',
            'uid' => 'nullable|string',
        ]);

        $order = Order::where('track_id', $validated['track_id'])->first();

        if (!$order) {
            Log::warning('Webhook: Order not found', ['track_id' => $validated['track_id']]);
            return response()->json(['error' => 'Order not found'], 404);
        }

        $autoVoucher = AutoVoucher::where('order_id', $order->id)->first();

        DB::transaction(function () use ($order, $autoVoucher, $validated) {
            $providerData = $order->provider_data ?? [];
            $providerData['webhook_status'] = $validated['status'];
            $providerData['webhook_message'] = $validated['message'] ?? null;
            $providerData['webhook_uid'] = $validated['uid'] ?? null;
            $order->provider_data = $providerData;

            if ($validated['status'] === 'success') {
                $order->status = OrderStatus::COMPLETED;
                $order->delivery_message = 'Topup completed successfully via webhook.';
            } elseif ($validated['status'] === 'failed') {
                $order->status = OrderStatus::PROCESSING;
                $order->voucher_code = null;

                if ($autoVoucher) {
                    $autoVoucher->order_id = null;
                    $autoVoucher->status = Status::AVAILABLE;
                    $autoVoucher->save();
                }
            }
            $order->save();
        });

        try {
            if (gs()->smtp_from_address && gs()->smtp_host && gs()->smtp_password && gs()->smtp_port && gs()->smtp_username) {
                Mail::to($order->user->email)->queue(new OrderStatusChanged($order));
            }
        } catch (\Exception $e) {
            Log::warning('Webhook: Email notification failed', ['order_id' => $order->id, 'error' => $e->getMessage()]);
        }

        return response()->json(['status' => 'ok']);
    }
}
