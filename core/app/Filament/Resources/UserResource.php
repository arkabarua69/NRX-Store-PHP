<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use App\Constants\Role;
use Filament\Forms\Get;
use Filament\Forms\Form;
use App\Constants\Status;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Hash;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\QueryBuilder;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Columns\TextInputColumn;
use App\Filament\Resources\UserResource\Pages;
use Filament\Tables\Filters\QueryBuilder\Constraints\DateConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\TextConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\NumberConstraint;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Select::make('role')
                            ->options([
                                Role::USER => 'User',
                                Role::ADMIN => 'Admin'
                            ])->required()->default(Role::USER),
                        TextInput::make('email')
                            ->required()
                            ->maxLength(255)
                            ->email(),
                        TextInput::make('phone')
                            ->maxLength(255),
                        TextInput::make('balance')
                            ->required()
                            ->maxLength(16)
                            ->default(0),
                        TextInput::make('password')
                            ->password()
                            ->dehydrateStateUsing(fn (string $state): string => Hash::make($state))
                            ->dehydrated(fn (?string $state): bool => filled($state))
                            ->required(fn (string $operation): bool => $operation === 'create'),
                        Toggle::make('status')
                            ->required()
                            ->default(Status::ACTIVE),
                        Toggle::make('is_reseller')
                            ->label('Reseller')
                            ->required()
                            ->default(Status::INACTIVE)
                    ])->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault(),
                TextColumn::make('name')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('role')
                    ->sortable()
                    ->searchable()
                    ->toggleable()
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        Role::USER => 'success',
                        Role::ADMIN => 'info',
                    })
                    ->formatStateUsing(fn (string $state): string => __(strtoupper($state))),
                TextColumn::make('email')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('phone')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                TextInputColumn::make('balance')
                    ->sortable()
                    ->searchable()
                    ->toggleable()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->money(gs()->base_currency),
                    ]),
                ToggleColumn::make('status')
                    ->sortable()
                    ->toggleable(),
                ToggleColumn::make('is_reseller')
                    ->label('Reseller')
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
                SelectFilter::make('role')
                    ->options([
                        Role::USER => 'User',
                        Role::ADMIN => 'Admin'
                    ]),
                SelectFilter::make('status')
                    ->options([
                        Status::ACTIVE => 'Active',
                        Status::INACTIVE => 'Inactive'
                    ]),
                QueryBuilder::make()
                    ->constraints([
                        TextConstraint::make('name'),
                        TextConstraint::make('email'),
                        TextConstraint::make('phone'),
                        NumberConstraint::make('balance'),
                        DateConstraint::make('created_at')
                            ->label('Created Date'),
                    ])
                    ->constraintPickerColumns(2),
            ], layout: FiltersLayout::AboveContentCollapsible)
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->hidden(fn ($record): bool => $record->role === Role::ADMIN),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    //Tables\Actions\DeleteBulkAction::make()
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
