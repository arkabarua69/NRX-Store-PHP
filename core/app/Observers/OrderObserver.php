<?php

namespace App\Observers;

use App\Helpers\ActivityLogger;
use App\Models\Order;
use App\Services\PusherBeamsService;
use Illuminate\Support\Facades\Log;

class OrderObserver
{
    public function created(Order $order): void
    {
        ActivityLogger::log(
            "Order #{$order->id} created - {$order->product?->title} ({$order->variation?->title})",
            $order,
            logName: 'order',
            event: 'created',
            properties: ['amount' => $order->amount, 'status' => $order->status]
        );
    }

    public function updated(Order $order): void
    {
        if ($order->isDirty('status')) {
            $old = $order->getOriginal('status');
            $new = $order->status;
            ActivityLogger::log(
                "Order #{$order->id} status changed from {$old} to {$new}",
                $order,
                logName: 'order',
                event: 'status_changed',
                properties: ['from' => $old, 'to' => $new]
            );

            try {
                $beams = new PusherBeamsService();
                $statusMessages = [
                    'completed' => 'Your order has been completed successfully!',
                    'processing' => 'Your order is now being processed.',
                    'cancelled' => 'Your order has been cancelled.',
                ];
                $message = $statusMessages[$new->value] ?? "Order status updated to {$new->value}";
                $beams->sendOrderNotification(
                    "Order #{$order->id}",
                    $message,
                    $order->id
                );
            } catch (\Exception $e) {
                Log::error('Push notification failed: ' . $e->getMessage());
            }
        }
    }

    public function deleted(Order $order): void
    {
        ActivityLogger::log(
            "Order #{$order->id} deleted",
            $order,
            logName: 'order',
            event: 'deleted'
        );
    }
}
