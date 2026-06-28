<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Constants\Role;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use App\Filament\Resources\UserResource;
use Filament\Resources\Pages\ListRecords;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

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
            'user' => Tab::make()->query(fn ($query) => $query->where('role', Role::USER)),
            'admin' => Tab::make()->query(fn ($query) => $query->where('role', Role::ADMIN)),
        ];
    }
}
