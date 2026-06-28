<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Constants\Status;
use App\Filament\Resources\OrderResource;
use Filament\Pages\Concerns\ExposesTableToWidgets;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;

class ListOrders extends ListRecords
{
    use ExposesTableToWidgets;

    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //Actions\CreateAction::make(),
        ];
    }

    public function getHeaderWidgets(): array
    {
        return OrderResource::getWidgets();
    }

    public function getTabs(): array
    {
        $data = [
            null         => Tab::make('All'),
            'completed'  => Tab::make()->query(fn($query) => $query->where('status', Status::COMPLETED)),
            'processing' => Tab::make()->query(fn($query) => $query->where('status', Status::PROCESSING)),
            'hold'       => Tab::make()->query(fn($query) => $query->where('status', Status::HOLD)),
            'pending'    => Tab::make()->query(fn($query) => $query->where('status', Status::PENDING)),
            'cancelled'  => Tab::make()->query(fn($query) => $query->where('status', Status::CANCELLED)),
        ];

        if (gs()->enable_auto_topup) {
            $data['auto-processing'] = Tab::make()->query(fn($query) => $query->where('status', Status::AUTOPROCESSING));
        }

        return $data;
    }
}
