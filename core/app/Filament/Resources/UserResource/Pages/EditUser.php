<?php

namespace App\Filament\Resources\UserResource\Pages;

use Filament\Actions;
use App\Constants\Role;
use App\Filament\Resources\UserResource;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->hidden(fn ($record): bool => $record->role === Role::ADMIN),
        ];
    }
}
