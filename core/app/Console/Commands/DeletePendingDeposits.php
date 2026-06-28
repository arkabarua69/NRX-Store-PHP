<?php

namespace App\Console\Commands;

use App\Constants\Status;
use App\Models\Deposit;
use Illuminate\Console\Command;

class DeletePendingDeposits extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'deposits:delete-pending';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete pending deposits older than 72 hours';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $cutoffTime = now()->subHours(72);

        $deletedDeposits = Deposit::where('status', Status::UNPAID)
            ->where('created_at', '<', $cutoffTime)
            ->delete();

        $this->info("Deleted $deletedDeposits pending deposits.");

        return 0;
    }
}