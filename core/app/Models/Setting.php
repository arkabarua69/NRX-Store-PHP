<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $casts = [
        'locked'    => 'boolean',
    ];

    public static function get(string $property)
    {
        $setting = self::query()
            ->where('name', $property)
            ->first('payload');

        return json_decode($setting->getAttribute('payload'));
    }
}
