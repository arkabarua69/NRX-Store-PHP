<?php

namespace App\Filament\Resources\AutoVoucherResource\Pages;

use Filament\Actions;
use App\Constants\Status;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\AutoVoucherResource;

class ListAutoVouchers extends ListRecords
{
    protected static string $resource = AutoVoucherResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            null => Tab::make('All'),
            'available' => Tab::make()->query(fn ($query) => $query->where('status', Status::AVAILABLE)),
            'sold' => Tab::make()->query(fn ($query) => $query->where('status', Status::SOLD)),
        ];
    }
}
