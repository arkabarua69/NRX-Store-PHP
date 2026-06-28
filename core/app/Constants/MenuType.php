<?php

namespace App\Constants;

class MenuType
{
    public const USER = 'user';
    public const GUEST = 'guest';
    public const BOTH = 'both';

    public const LIST = [
        self::USER,
        self::GUEST,
        self::BOTH,
    ];
}
