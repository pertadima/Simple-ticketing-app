<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EventCategoriesResource\Pages;
use App\Models\EventCategories;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EventCategoriesResource extends Resource
{
    protected static ?string $model = EventCategories::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationGroup = 'Event Management';

    protected static ?string $navigationLabel = 'Categories';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Category Information')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Textarea::make('description')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('category_id')
                    ->label('ID')
                    ->sortable(),
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),
                TextColumn::make('description')
                    ->limit(50)
                    ->tooltip(function (EventCategories $record): ?string {
                        return $record->description;
                    }),
                TextColumn::make('events_count')
                    ->counts('events')
                    ->label('Events')
                    ->sortable()
                    ->badge()
                    ->color('success'),
                TextColumn::make('created_at')
                    ->dateTime('M j, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->striped()
            ->paginated([10, 25, 50]);
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
            'index' => Pages\ListEventCategories::route('/'),
            'create' => Pages\CreateEventCategories::route('/create'),
            'edit' => Pages\EditEventCategories::route('/{record}/edit'),
        ];
    }
}
