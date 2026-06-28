<?php

namespace App\Filament\Resources\CodeResource\Widgets;

use App\Models\Order;
use App\Constants\Status;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use App\Filament\Resources\CodeResource\Pages\ListCodes;
use Illuminate\Database\Eloquent\Builder;

class CodeStats extends BaseWidget
{
    use InteractsWithPageTable;

    protected static ?string $pollingInterval = null;

    protected function getTablePage(): string
    {
        return ListCodes::class;
    }

    protected function getStats(): array
    {
        $orderData = Trend::query(
            Order::query()
                ->whereHas('product', function ($query) {
                    $query->where('type', Status::VOUCHER);
                })
        )
            ->between(
                start: now()->subYear(),
                end: now(),
            )
            ->perMonth()
            ->count();

        return [
            Stat::make('Orders', $this->getPageTableQuery()->count())
                ->chart(
                    $orderData
                        ->map(fn (TrendValue $value) => $value->aggregate)
                        ->toArray()
                ),
            Stat::make('Completed orders', $this->getPageTableQuery()->where('status', Status::COMPLETED)->count()),
            Stat::make('Average price', number_format($this->getPageTableQuery()->avg('amount'), 2)),
        ];
    }
}
