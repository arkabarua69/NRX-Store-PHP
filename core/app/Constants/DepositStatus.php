<?php

namespace App\Constants;

class DepositStatus
{
    public const PAID = 'paid';
    public const UNPAID = 'unpaid';

    public static function color($status): string
    {
        return match ($status) {
            self::PAID => 'text-success',
            self::UNPAID => 'text-danger',
        };
    }
}
