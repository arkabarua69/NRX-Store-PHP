<?php

namespace App\Console\Commands;

use App\Constants\Status;
use App\Models\Deposit;
use Illuminate\Database\Eloquent\Builder;

class DeletePendingDeposits extends DeletePendingRecords
{
    protected $signature = 'deposits:delete-pending';

    protected $description = 'Delete pending deposits older than 72 hours';

    protected function getQuery(): Builder
    {
        return Deposit::where('status', Status::UNPAID);
    }

    protected function getRecordLabel(): string
    {
        return 'deposits';
    }
}
