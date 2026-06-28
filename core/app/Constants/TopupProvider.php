<?php

namespace App\Constants;

class TopupProvider
{
    public const FREEFIRE = 'FreeFire';
    public const UNIPIN = 'Unipin';

    public const OPTIONS = [
        self::FREEFIRE => 'Free Fire',
        self::UNIPIN => 'Unipin',
    ];

    public const PRODUCTVARIATIONS = [
        '0' => '25 Diamond',
        '1' => '50 Diamond',
        '2' => '115 Diamond',
        '3' => '240 Diamond',
        '4' => '610 Diamond',
        '5' => '1240 Diamond',
        '6' => '2530 Diamond',
        '7' => 'Weekly Membership',
        '8' => 'Monthly Membership',
        '9' => 'Level Up Pass',
    ];
}
