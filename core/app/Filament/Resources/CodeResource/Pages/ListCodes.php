<?php

namespace App\Filament\Resources\CodeResource\Pages;

use Filament\Actions;
use App\Constants\Status;
use Filament\Resources\Components\Tab;
use App\Filament\Resources\CodeResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Pages\Concerns\ExposesTableToWidgets;

class ListCodes extends ListRecords
{
    use ExposesTableToWidgets;
    
    protected static string $resource = CodeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //Actions\CreateAction::make(),
        ];
    }

    public function getHeaderWidgets(): array
    {
        return CodeResource::getWidgets();
    }

    public function getTabs(): array
    {
        return [
            null => Tab::make('All'),
            'completed' => Tab::make()->query(fn ($query) => $query->where('status', Status::COMPLETED)),
            'hold' => Tab::make()->query(fn ($query) => $query->where('status', Status::HOLD)),
            'pending' => Tab::make()->query(fn ($query) => $query->where('status', Status::PENDING)),
            'cancelled' => Tab::make()->query(fn ($query) => $query->where('status', Status::CANCELLED)),
        ];
    }
}
