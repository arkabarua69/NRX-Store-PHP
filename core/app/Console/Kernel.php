<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Artisan;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->call(function () {
            Artisan::call('orders:delete-pending');
            Artisan::call('deposits:delete-pending');
        })->hourly()->name('delete-pending');

        $schedule->call(function () {
            Artisan::call('orders:dispatch');
        })->everyMinute()->name('orders');

        $schedule->call(function () {
            Artisan::call('queue:work', [
                '--queue'           => 'order',
                '--tries'           => 3,
                '--stop-when-empty' => true,
            ]);
        })->everyMinute()->name('order-queue')->withoutOverlapping();

        $schedule->call(function () {
            Artisan::call('queue:work', [
                '--queue'           => 'default',
                '--tries'           => 3,
                '--stop-when-empty' => true,
            ]);
        })->everyMinute()->name('default-queue')->withoutOverlapping();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
