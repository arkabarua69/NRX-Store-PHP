<?php

namespace App\Console\Commands;

use App\Constants\Status;
use App\Models\Order;
use Illuminate\Console\Command;

class DeletePendingOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:delete-pending';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete pending orders older than 72 hours';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $cutoffTime = now()->subHours(72);

        $deletedOrders = Order::where('status', Status::PENDING)
            ->where('created_at', '<', $cutoffTime)
            ->delete();

        $this->info("Deleted $deletedOrders pending orders.");

        return 0;
    }
}
