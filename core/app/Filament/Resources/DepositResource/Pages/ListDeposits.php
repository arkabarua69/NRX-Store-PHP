<?php

namespace App\Filament\Resources\DepositResource\Pages;

use Filament\Actions;
use App\Constants\Status;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\DepositResource;

class ListDeposits extends ListRecords
{
    protected static string $resource = DepositResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            null => Tab::make('All'),
            'paid' => Tab::make()->query(fn ($query) => $query->where('status', Status::PAID)),
            'unpaid' => Tab::make()->query(fn ($query) => $query->where('status', Status::UNPAID)),
        ];
    }
}
