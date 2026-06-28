<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use App\Constants\Status;
use Filament\Tables\Table;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class TransactionRelationManager extends RelationManager
{
    protected static string $relationship = 'transaction';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                // Hidden::make('user_id')
                //     ->default(function (RelationManager $livewire) {
                //         return $livewire->getOwnerRecord()->user->id;
                //     }),
                TextInput::make('amount')
                    ->required()
                    ->numeric()
                    ->inputMode('decimal')
                    ->disabled(),
                TextInput::make('transaction_id')
                    ->label('Transaction ID')
                    ->required()
                    ->maxLength(255)
                    ->disabled(),
                TextInput::make('payment_method')
                    ->label('Payment Method')
                    ->required()
                    ->maxLength(255)
                    ->disabled(),
                TextInput::make('remarks')
                    ->required()
                    ->maxLength(255)
                    ->disabled(),
                Select::make('trx_type')
                    ->label('Type')
                    ->options([
                        Status::DEBIT => 'Debit',
                        Status::CREDIT => 'Credit'
                    ])->required()->default(Status::CREDIT)
                    ->disabled(),
            ]);
    }


    public function table(Table $table): Table
    {
        return $table
            //->recordTitleAttribute('transaction_id')
            ->columns([
                TextColumn::make('user.name')
                    ->label('User'),
                TextColumn::make('amount'),
                TextColumn::make('transaction_id')
                    ->label('Transaction ID'),
                TextColumn::make('payment_method')
                    ->label('Payment Method'),
                TextColumn::make('remarks'),
                TextColumn::make('trx_type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        Status::CREDIT => 'success',
                        Status::DEBIT => 'info'
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        Status::CREDIT => 'Credit',
                        Status::DEBIT => 'Debit'
                    }),
                TextColumn::make('created_at')
                    ->label('Date')
                    ->date(),
            ])
            // ->filters([
            //     //
            // ])
            // ->headerActions([
            //     Tables\Actions\CreateAction::make(),
            // ])
            ->actions([
               Tables\Actions\EditAction::make(),
                //Tables\Actions\DeleteAction::make(),
            ])
            ->paginated(false);
        // ->bulkActions([
        //     Tables\Actions\BulkActionGroup::make([
        //         Tables\Actions\DeleteBulkAction::make(),
        //     ]),
        // ]);
    }
}
