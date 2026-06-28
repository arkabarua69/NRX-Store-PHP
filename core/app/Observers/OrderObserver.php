<?php

namespace App\Observers;

use App\Helpers\ActivityLogger;
use App\Models\Order;
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
