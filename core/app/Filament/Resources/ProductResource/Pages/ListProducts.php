<?php

namespace App\Filament\Resources\ProductResource\Pages;

use Filament\Actions;
use App\Constants\Status;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\ProductResource;
use Illuminate\Contracts\Pagination\CursorPaginator;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    // protected function paginateTableQuery(Builder $query): CursorPaginator
    // {
    //     return $query->cursorPaginate(($this->getTableRecordsPerPage() === 'all') ? $query->count() : $this->getTableRecordsPerPage());
    // }
    public function getTabs(): array
    {
        return [
            null => Tab::make('All'),
            'topup' => Tab::make()->query(fn ($query) => $query->where('type', Status::TOPUP)),
            'ingame' => Tab::make()->label('In Game')->query(fn ($query) => $query->where('type', Status::INGAME)),
            'voucher' => Tab::make()->query(fn ($query) => $query->where('type', Status::VOUCHER)),
        ];
    }
}
