<?php

namespace App\Console\Commands;

use App\Constants\Status;
use App\Models\Order;
use Illuminate\Database\Eloquent\Builder;

class DeletePendingOrders extends DeletePendingRecords
{
    protected $signature = 'orders:delete-pending';

    protected $description = 'Delete pending orders older than 72 hours';

    protected function getQuery(): Builder
    {
        return Order::where('status', Status::PENDING);
    }

    protected function getRecordLabel(): string
    {
        return 'orders';
    }
}
