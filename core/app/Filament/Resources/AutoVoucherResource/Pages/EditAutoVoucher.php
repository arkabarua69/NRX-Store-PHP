<?php

namespace App\Filament\Resources\AutoVoucherResource\Pages;

use App\Filament\Resources\AutoVoucherResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAutoVoucher extends EditRecord
{
    protected static string $resource = AutoVoucherResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
