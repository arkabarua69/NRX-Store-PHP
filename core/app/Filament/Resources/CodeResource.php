<?php

namespace App\Filament\Resources;

use App\Constants\OrderStatus;
use App\Constants\Status;
use App\Filament\Resources\CodeResource\Pages;
use App\Filament\Resources\CodeResource\RelationManagers\TransactionRelationManager;
use App\Filament\Resources\CodeResource\Widgets\CodeStats;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Carbon;

class CodeResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-gift';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'Codes';

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
                                Status::PROCESSING => 'Processing',
                                Status::HOLD       => 'Hold',
                                Status::COMPLETED  => 'Completed',
                                Status::PENDING    => 'Pending',
                                Status::CANCELLED  => 'Cancelled',
                            ])->required()->default(Status::ACTIVE),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('user.name')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('product.title')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('variation.title')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('voucher_code')
                    ->sortable()
                    ->searchable()
                    ->toggleable()
                    ->badge()
                    ->separator(',')
                    ->copyable()
                    ->copyMessage('Code copied')
                    ->copyMessageDuration(1500),
                TextColumn::make('amount')
                    ->sortable()
                    ->searchable()
                    ->toggleable()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->money(gs()->base_currency),
                    ]),
                TextColumn::make('status')
                    ->sortable()
                    ->searchable()
                    ->toggleable()
                    ->badge()
                    ->color(fn(string $state): string => OrderStatus::adminColor($state))
                    ->formatStateUsing(fn(string $state): string => __(strtoupper($state))),
                TextColumn::make('created_at')
                    ->label('Order Date')
                    ->date()
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault(),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                SelectFilter::make('status')
                    ->options(OrderStatus::options()),
                Tables\Filters\Filter::make('created_from')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->placeholder(fn($state): string => 'Dec 18, ' . now()->subYear()->format('Y')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'] ?? null,
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['created_from'] ?? null) {
                            $indicators['created_from'] = 'Order from ' . Carbon::parse($data['created_from'])->toFormattedDateString();
                        }

                        return $indicators;
                    }),

                Tables\Filters\Filter::make('created_until')
                    ->form([
                        Forms\Components\DatePicker::make('created_until')
                            ->placeholder(fn($state): string => now()->format('M d, Y')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_until'] ?? null,
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['created_until'] ?? null) {
                            $indicators['created_until'] = 'Order until ' . Carbon::parse($data['created_until'])->toFormattedDateString();
                        }

                        return $indicators;
                    }),
            ], layout: FiltersLayout::AboveContentCollapsible)
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                ]),
            ])
            ->deferLoading()
            ->paginated([10, 25, 50, 100, 200, 500, 1000]);
    }

    public static function getRelations(): array
    {
        return [
            TransactionRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCodes::route('/'),
            //'create' => Pages\CreateCode::route('/create'),
            'edit'  => Pages\EditCode::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['variation', 'product', 'voucher', 'user'])
            ->whereHas('product', function (Builder $query) {
                $query->where('type', Status::VOUCHER);
            })
            ->withoutGlobalScope(SoftDeletingScope::class);
    }

    public static function getWidgets(): array
    {
        return [
            CodeStats::class,
        ];
    }
}
