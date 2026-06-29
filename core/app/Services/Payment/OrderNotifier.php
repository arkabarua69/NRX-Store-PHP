<?php

namespace App\Services\Payment;

use App\Constants\Role;
use App\Mail\OrderPlaced;
use App\Mail\OrderStatusChanged;
use App\Models\Order;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class OrderNotifier
{
    public static function isSmtpConfigured(): bool
    {
        return gs()->smtp_from_address
            && gs()->smtp_host
            && gs()->smtp_password
            && gs()->smtp_port
            && gs()->smtp_username;
    }

    public static function notifyAdminsOrderPlaced(Order $order): void
    {
        if (! self::isSmtpConfigured()) {
            return;
        }

        try {
            foreach (User::where('role', Role::ADMIN)->cursor() as $admin) {
                Mail::to($admin->email)->queue(new OrderPlaced($order));
            }
        } catch (Exception $e) {
            Log::warning('Order notification mail to admins failed', [
                'order' => $order->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public static function notifyUserStatusChanged(Order $order): void
    {
        if (! self::isSmtpConfigured()) {
            return;
        }

        try {
            Mail::to($order->user->email)->queue(new OrderStatusChanged($order));
        } catch (Exception $e) {
            Log::warning('Order status notification mail failed', [
                'order' => $order->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public static function notifyOrderCompleted(Order $order): void
    {
        self::notifyAdminsOrderPlaced($order);
        self::notifyUserStatusChanged($order);
    }
}
