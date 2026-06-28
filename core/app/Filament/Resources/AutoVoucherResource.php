<?php

namespace App\Filament\Resources;

use App\Constants\Status;
use App\Filament\Resources\AutoVoucherResource\Pages;
use App\Models\AutoVoucher;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AutoVoucherResource extends Resource
{
    protected static ?string $model = AutoVoucher::class;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';

    protected static ?int $navigationSort = 11;

    protected static ?string $navigationGroup = 'Products';

    public static function canViewAny(): bool
    {
        //return true;
        return gs()->enable_auto_topup;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        Select::make('variation_id')
                            ->relationship('variation', titleAttribute: 'title', modifyQueryUsing: fn(Builder $query) => $query->where('automatic', 1)->whereHas('product', function (Builder $query) {
                                $query->where('type', Status::TOPUP);
                            }))
                            ->searchable()
                            ->preload()
                            ->required(),
                        TagsInput::make('code')
                            ->splitKeys(['Tab', ','])
                            ->required()
                            ->placeholder('Code'),
                        Toggle::make('status')
                            ->label('Available')
                            ->required()         
                            ->default(true),
                    ])->columns(2),
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
                TextColumn::make('variation.product.title')
                    ->label('Product')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
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
                TextColumn::make('order_id')
                    ->label('Order Id')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('transaction_id')
                    ->label('Transaction ID')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('Date')
                    ->date()
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault(),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                SelectFilter::make('variation')
                    ->relationship('variation', titleAttribute: 'title', modifyQueryUsing: fn(Builder $query) => $query->whereHas('product', function (Builder $query) {
                        $query->where('type', Status::VOUCHER);
                    }))
                    ->searchable()
                    ->preload(),
                SelectFilter::make('status')
                    ->options([
                        Status::AVAILABLE => 'Available',
                        Status::SOLD      => 'Sold',
                    ]),
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['variation.product']);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListAutoVouchers::route('/'),
            'create' => Pages\CreateAutoVoucher::route('/create'),
            'edit'   => Pages\EditAutoVoucher::route('/{record}/edit'),
        ];
    }
}