<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Deposit;
use Filament\Forms\Form;
use App\Constants\Status;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\QueryBuilder;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\DepositResource\Pages;
use Filament\Tables\Filters\QueryBuilder\Constraints\DateConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\NumberConstraint;
use App\Filament\Resources\DepositResource\RelationManagers\TransactionRelationManager;

class DepositResource extends Resource
{
    protected static ?string $model = Deposit::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationGroup = 'Payments';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        TextInput::make('amount')
                            ->required()
                            ->maxLength(16)
                            ->disabled(),
                        Select::make('status')
                            ->options([
                                Status::PAID => 'Paid',
                                Status::UNPAID => 'Unpaid',
                            ])->required(),
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
                TextColumn::make('transaction.payment_method')
                    ->label('Payment Method')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('transaction.transaction_id')
                    ->label('Transaction ID')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('status')
                    ->sortable()
                    ->searchable()
                    ->toggleable()
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        Status::PAID => 'success',
                        Status::UNPAID => 'warning'
                    })
                    ->formatStateUsing(fn (string $state): string => __(strtoupper($state))),
                TextColumn::make('created_at')
                    ->label('Date')
                    ->date()
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault(),
                TextColumn::make('track_id')
                    ->label('Track ID')
                    ->sortable()
                ->searchable()
                    ->toggleable(),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        Status::PAID => 'Paid',
                        Status::UNPAID => 'Unpaid'
                    ]),
                SelectFilter::make('user')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
                QueryBuilder::make()
                    ->constraints([
                        NumberConstraint::make('amount'),
                        DateConstraint::make('created_at'),
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
            TransactionRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDeposits::route('/'),
            //'create' => Pages\CreateDeposit::route('/create'),
            'edit' => Pages\EditDeposit::route('/{record}/edit'),
        ];
    }
}
