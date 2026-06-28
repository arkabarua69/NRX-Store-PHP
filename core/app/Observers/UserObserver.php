<?php

namespace App\Observers;

use App\Helpers\ActivityLogger;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class UserObserver
{
    public function created(User $user): void
    {
        ActivityLogger::log(
            "User {$user->name} ({$user->email}) created as {$user->role}",
            $user,
            logName: 'user',
            event: 'created'
        );
    }

    public function updated(User $user): void
    {
        $changes = [];
        foreach ($user->getDirty() as $key => $value) {
            if (!in_array($key, ['password', 'remember_token', 'updated_at'])) {
                $changes[$key] = [
                    'from' => $user->getOriginal($key),
                    'to' => $value,
                ];
            }
        }
        if (!empty($changes)) {
            ActivityLogger::log(
                "User {$user->name} updated - " . json_encode($changes),
                $user,
                logName: 'user',
                event: 'updated',
                properties: $changes
            );
        }
    }
}
