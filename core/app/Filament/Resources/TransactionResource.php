<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use App\Constants\Status;
use Filament\Tables\Table;
use App\Models\Transaction;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\QueryBuilder;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\TransactionResource\Pages;
use Filament\Forms\Components\Section;
use Filament\Tables\Filters\QueryBuilder\Constraints\DateConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\TextConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\NumberConstraint;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';

    protected static ?int $navigationSort = 4;

    protected static ?string $navigationGroup = 'Payments';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        Select::make('user_id')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->disabled(fn ($livewire) => $livewire instanceof \Filament\Resources\Pages\EditRecord),
                        TextInput::make('amount')
                            ->required()
                            ->numeric()
                            ->inputMode('decimal')
                            ->disabled(fn ($livewire) => $livewire instanceof \Filament\Resources\Pages\EditRecord),
                        TextInput::make('transaction_id')
                            ->label('Transaction ID')
                            ->required()
                            ->maxLength(255)
                            ->disabled(fn ($livewire) => $livewire instanceof \Filament\Resources\Pages\EditRecord),
                        TextInput::make('payment_method')
                            ->label('Payment Method')
                            ->required()
                            ->maxLength(255)
                            ->disabled(fn ($livewire) => $livewire instanceof \Filament\Resources\Pages\EditRecord),
                        TextInput::make('remarks')
                            ->required()
                            ->maxLength(255)
                            ->disabled(fn ($livewire) => $livewire instanceof \Filament\Resources\Pages\EditRecord),
                        Select::make('trx_type')
                            ->label('Type')
                            ->options([
                                Status::DEBIT => 'Debit',
                                Status::CREDIT => 'Credit'
                            ])->required()->default(Status::CREDIT)
                            ->disabled(fn ($livewire) => $livewire instanceof \Filament\Resources\Pages\EditRecord),
                    ])->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('user.name')
                    ->label('User')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('amount')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('transaction_id')
                    ->label('Transaction ID')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('payment_method')
                    ->label('Payment Method')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('remarks')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('trx_type')
                    ->label('Type')
                    ->sortable()
                    ->searchable()
                    ->toggleable()
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
                    ->date()
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault(),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                SelectFilter::make('trx_type')
                    ->label('Type')
                    ->options([
                        Status::CREDIT => 'Credit',
                        Status::DEBIT => 'Debit'
                    ]),
                SelectFilter::make('user')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
                QueryBuilder::make()
                    ->constraints([
                        NumberConstraint::make('amount'),
                        TextConstraint::make('payment_method'),
                        DateConstraint::make('created_at')
                            ->label('Date'),
                    ])
                    ->constraintPickerColumns(2),
            ], layout: FiltersLayout::AboveContentCollapsible)
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

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('user');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTransactions::route('/'),
            'create' => Pages\CreateTransaction::route('/create'),
            'edit' => Pages\EditTransaction::route('/{record}/edit'),
        ];
    }
}
