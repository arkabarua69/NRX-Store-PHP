<?php

namespace App\Constants;

class OrderStatus
{
    // order status
    public const PENDING = 'pending';
    public const CANCELLED = 'cancelled';
    public const COMPLETED = 'completed';
    public const PROCESSING = 'processing';
    public const AUTOPROCESSING = 'auto-processing';
    public const HOLD = 'hold';

    public const ORDERLIST = [
        self::COMPLETED,
        self::PROCESSING,
        self::AUTOPROCESSING,
        self::HOLD,
        self::PENDING,
        self::CANCELLED,
    ];

    public static function options(): array
    {
        $data = [
            self::COMPLETED  => 'Completed',
            self::PROCESSING => 'Processing',
            self::HOLD       => 'Hold',
            self::PENDING    => 'Pending',
            self::CANCELLED  => 'Cancelled',
        ];

        if (gs()->enable_auto_topup) {
            $data[self::AUTOPROCESSING] = 'Auto Processing';
        }

        return $data;
    }

    public static function color($status): string
    {
        return match ($status) {
            self::COMPLETED => 'text-success',
            self::PROCESSING => 'text-primary',
            self::AUTOPROCESSING => 'text-info',
            self::HOLD => 'text-warning',
            self::PENDING => 'text-warning',
            self::CANCELLED => 'text-danger',
        };
    }

    public static function adminColor($status): string
    {
        return match ($status) {
            self::COMPLETED => 'success',
            self::PROCESSING => 'info',
            self::AUTOPROCESSING => 'gray',
            self::HOLD => 'warning',
            self::PENDING => 'warning',
            self::CANCELLED => 'danger',
        };
    }
}
