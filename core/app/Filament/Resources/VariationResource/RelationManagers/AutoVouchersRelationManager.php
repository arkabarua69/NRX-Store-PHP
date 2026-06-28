<?php

namespace App\Filament\Resources\VariationResource\RelationManagers;

use App\Constants\Status;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class AutoVouchersRelationManager extends RelationManager
{
    protected static string $relationship = 'autoVouchers';

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return $ownerRecord->product->type === Status::TOPUP && $ownerRecord->automatic && gs()->enable_auto_topup;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('code')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
        //->recordTitleAttribute('code')
            ->columns([
                TextColumn::make('variation.title')
                    ->label('Variation')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('code')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                ToggleColumn::make('status')
                    ->label('Available')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->deferLoading()
            ->paginated([10, 25, 50, 100, 200, 500, 1000]);
    }
}
