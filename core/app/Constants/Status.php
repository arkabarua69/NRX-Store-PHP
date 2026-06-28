<?php

namespace App\Constants;

class Status
{
    // status
    public const ACTIVE = 1;
    public const INACTIVE = 0;
    public const DEFAULT = 1;

    // type
    public const ONCE = 'once';
    public const DAILY = 'daily';

    // Transaction
    public const CREDIT = '-';
    public const DEBIT = '+';

    // invoice status
    public const PAID = 'paid';
    public const UNPAID = 'unpaid';

    // order status
    public const PENDING = 'pending';
    public const CANCELLED = 'cancelled';
    public const COMPLETED = 'completed';
    public const PROCESSING = 'processing';
    public const AUTOPROCESSING = 'auto-processing';
    public const HOLD = 'hold';
    public const WALLET = 'wallet';

    public const ORDERLIST = [
        self::COMPLETED,
        self::PROCESSING,
        self::AUTOPROCESSING,
        self::HOLD,
        self::PENDING,
        self::CANCELLED
    ];

    // product type
    public const TOPUP = 'topup';
    public const INGAME = 'ingame';
    public const VOUCHER = 'voucher';

    // voucher status
    public const SOLD = 0;
    public const AVAILABLE = 1;
    public const ISVOUCHER = 1;
    public const NOTVOUCHER = 0;
}
