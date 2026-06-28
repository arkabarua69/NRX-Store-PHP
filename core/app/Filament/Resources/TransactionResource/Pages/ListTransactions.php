<?php

namespace App\Filament\Resources\TransactionResource\Pages;

use Filament\Actions;
use App\Constants\Status;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\TransactionResource;

class ListTransactions extends ListRecords
{
    protected static string $resource = TransactionResource::class;

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
            'debit' => Tab::make()->query(fn ($query) => $query->where('trx_type', Status::DEBIT)),
            'credit' => Tab::make()->query(fn ($query) => $query->where('trx_type', Status::CREDIT)),
        ];
    }
}
