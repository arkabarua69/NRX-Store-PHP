<?php

namespace App\Helpers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class ActivityLogger
{
    public static function log(
        string $description,
        ?Model $subject = null,
        ?User $causer = null,
        ?string $logName = 'default',
        ?string $event = null,
        ?array $properties = null
    ): ActivityLog {
        $causer = $causer ?? (auth()->check() ? auth()->user() : null);

        return ActivityLog::create([
            'log_name' => $logName,
            'description' => $description,
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id' => $subject?->getKey(),
            'causer_type' => $causer ? User::class : null,
            'causer_id' => $causer?->getKey(),
            'properties' => $properties,
            'event' => $event,
        ]);
    }

    public static function byAdmin(string $description, ?Model $subject = null, ?array $properties = null): ActivityLog
    {
        return self::log($description, $subject, null, 'admin', null, $properties);
    }

    public static function byUser(string $description, ?Model $subject = null, ?array $properties = null): ActivityLog
    {
        return self::log($description, $subject, null, 'user', null, $properties);
    }

    public static function forOrder(\App\Models\Order $order, string $event, string $description): ActivityLog
    {
        return self::log($description, $order, null, 'order', $event);
    }
}
