<?php

namespace App\Filament\Resources;

use App\Constants\MenuType;
use App\Constants\Status;
use App\Filament\Resources\MenuResource\Pages;
use App\Models\Menu;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MenuResource extends Resource
{
    protected static ?string $model = Menu::class;

    protected static ?string $navigationIcon = 'heroicon-o-bars-4';

    protected static ?int $navigationSort = 11;

    protected static ?string $navigationGroup = 'Frontend';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('link')
                            ->required()
                            ->maxLength(255),
                        Textarea::make('icon')
                            ->label('Icon (FontAwesome or SVG)')
                            ->required()
                            ->rows(4)
                            ->columnSpanFull(),
                        Select::make('type')
                            ->options([
                                MenuType::USER  => 'User',
                                MenuType::GUEST => 'Guest',
                                MenuType::BOTH  => 'Both',
                            ])->required()->default(MenuType::BOTH),
                        Toggle::make('status')->required()->default(Status::ACTIVE),
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
                TextColumn::make('order_column')
                    ->label('Order')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('name')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('link')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('type')
                    ->sortable()
                    ->searchable()
                    ->toggleable()
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        MenuType::USER                    => 'success',
                        MenuType::GUEST                   => 'primary',
                        MenuType::BOTH                    => 'info'
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        MenuType::USER                               => 'User',
                        MenuType::GUEST                              => 'Guest',
                        MenuType::BOTH                               => 'Both',
                    }),
                ToggleColumn::make('status')
                    ->sortable()
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
                SelectFilter::make('status')
                    ->options([
                        MenuType::USER  => 'User',
                        MenuType::GUEST => 'Guest',
                        MenuType::BOTH  => 'Both',
                    ]),
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
            ->paginated([10, 25, 50, 100, 200, 500, 1000])
            ->reorderable('order_column')
            ->paginatedWhileReordering();
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
            'index'  => Pages\ListMenus::route('/'),
            'create' => Pages\CreateMenu::route('/create'),
            'edit'   => Pages\EditMenu::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->orderBy('order_column');
    }
}
