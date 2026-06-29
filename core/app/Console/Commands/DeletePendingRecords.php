<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;

abstract class DeletePendingRecords extends Command
{
    abstract protected function getQuery(): Builder;

    abstract protected function getRecordLabel(): string;

    protected function getCutoffHours(): int
    {
        return 72;
    }

    public function handle(): int
    {
        $cutoffTime = now()->subHours($this->getCutoffHours());

        $deleted = $this->getQuery()
            ->where('created_at', '<', $cutoffTime)
            ->delete();

        $this->info("Deleted {$deleted} pending {$this->getRecordLabel()}.");

        return 0;
    }
}
