<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Product;
use Filament\Forms\Form;
use App\Constants\Status;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Forms\Components\RichEditor;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\QueryBuilder;
use Filament\Tables\Filters\SelectFilter;
use App\Filament\Resources\ProductResource\Pages;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Tables\Filters\QueryBuilder\Constraints\DateConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\TextConstraint;
use App\Filament\Resources\ProductResource\RelationManagers\VariationsRelationManager;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?int $navigationSort = 8;

    protected static ?string $navigationGroup = 'Products';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (string $operation, $state, Forms\Set $set) {
                                if ($operation !== 'create') {
                                    return;
                                }
                                $set('slug', Str::slug($state));
                            }),
                        TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(Product::class, 'slug', ignoreRecord: true),
                        RichEditor::make('content')->required()->maxLength(5000)->columnSpanFull(),
                        TextInput::make('percentage')
                            ->label('Reseller Percentage')
                            ->hintIcon('heroicon-m-question-mark-circle', 'Enter the percentage value. This percentage will be added to the user\'s account when they order this product.')
                            ->default(0),
                        Select::make('uid_checker')
                            ->label('UID Checker')
                            ->hintIcon('heroicon-m-question-mark-circle', 'Enable or disable the UID checker for Topup products.')
                            ->options([
                                0 => 'Disable',
                                1 => 'Enable'
                            ])
                            ->default(0),


                    Select::make('has_tutorial')
                        ->label('Has Tutorial')
                        ->options([
                            0 => 'No',
                            1 => 'Yes',
                        ])
                        ->default(0)
                         ->reactive()
                            ->hintIcon('heroicon-m-question-mark-circle', 'Add tutorial to show in the product checkout page.'),

                    TextInput::make('tutorial_link')
                        ->label('Tutorial Link')
                        ->url()
                        ->maxLength(1024)
                        ->visible(fn ($get) => $get('has_tutorial') == 1)
                         ->hintIcon('heroicon-m-question-mark-circle', 'Add tutorial link.')
                        ->required(fn ($get) => $get('has_tutorial') == 1),

                    TextInput::make('tutorial_text')
                        ->label('Tutorial Text')
                        ->maxLength(1024)
                        ->visible(fn ($get) => $get('has_tutorial') == 1)
                        ->hintIcon('heroicon-m-question-mark-circle', 'Add tutorial link text.')
                        ->required(fn ($get) => $get('has_tutorial') == 1),

                    ])->columnSpan(2)->columns(3),

                Section::make('Meta')
                    ->schema([
                        SpatieMediaLibraryFileUpload::make('thumbnail')
                            ->disk('media')
                            ->image()
                            ->maxSize(10240)
                            ->required(),
                        Select::make('type')
                            ->options([
                                Status::TOPUP => 'Topup',
                                Status::INGAME => 'IN Game',
                                Status::VOUCHER => 'Voucher'
                            ])->required(),
                        Select::make('status')
                            ->options([
                                Status::ACTIVE => 'Active',
                                Status::INACTIVE => 'Inactive'
                            ])->required()->default(Status::ACTIVE),
             
                    ])->columnSpan(1),
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('title')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('variations_count')->counts('variations')
                    ->label('Total Variations')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('type')
                    ->sortable()
                    ->searchable()
                    ->toggleable()
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        Status::INGAME => 'success',
                        Status::TOPUP => 'warning',
                        Status::VOUCHER => 'danger'
                    })
                    ->formatStateUsing(fn (string $state): string => __(strtoupper($state))),
                // TextColumn::make('status')
                //     ->sortable()
                //     ->searchable()
                //     ->toggleable()
                //     ->badge()
                //     ->color(fn (string $state): string => match ($state) {
                //         Status::ACTIVE => 'success',
                //         Status::INACTIVE => 'danger'
                //     })
                //     ->formatStateUsing(fn (string $state): string => __(strtoupper($state))),
                ToggleColumn::make('status')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('Publish Date')
                    ->date()
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault(),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                SelectFilter::make('type')
                    ->options([
                        Status::TOPUP => 'Topup',
                        Status::INGAME => 'IN Game',
                        Status::VOUCHER => 'Voucher'
                    ]),
                SelectFilter::make('status')
                    ->options([
                        Status::ACTIVE => 'Active',
                        Status::INACTIVE => 'Inactive'
                    ]),
                QueryBuilder::make()
                    ->constraints([
                        TextConstraint::make('title'),
                        TextConstraint::make('slug'),
                        TextConstraint::make('content'),
                        DateConstraint::make('created_at')
                            ->label('Publish Date'),
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
            ->paginated([10, 25, 50, 100, 200, 500, 1000])
            ->reorderable('order_column')
            ->paginatedWhileReordering();
    }

    public static function getRelations(): array
    {
        return [
            VariationsRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
