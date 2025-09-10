<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EventsResource\Pages;
use App\Models\Events;
use App\Models\EventCategories;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DateTimePicker;

class EventsResource extends Resource
{
    protected static ?string $model = Events::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationGroup = 'Event Management';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Event Details')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        DateTimePicker::make('date')
                            ->required()
                            ->native(false),
                        TextInput::make('location')
                            ->required()
                            ->maxLength(255),
                        Textarea::make('description')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                
                Section::make('Categories')
                    ->schema([
                        Select::make('categories')
                            ->relationship('categories', 'name')
                            ->multiple()
                            ->preload()
                            ->createOptionForm([
                                TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),
                                Textarea::make('description')
                                    ->rows(3),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('event_id')
                    ->label('ID')
                    ->sortable(),
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),
                TextColumn::make('date')
                    ->dateTime('M j, Y g:i A')
                    ->sortable()
                    ->badge()
                    ->color(fn ($record) => $record->date->isPast() ? 'danger' : 'success'),
                TextColumn::make('location')
                    ->searchable()
                    ->limit(30),
                TextColumn::make('categories.name')
                    ->badge()
                    ->separator(',')
                    ->limit(50),
                TextColumn::make('tickets_count')
                    ->counts('tickets')
                    ->label('Tickets')
                    ->sortable()
                    ->badge()
                    ->color('info'),
                TextColumn::make('created_at')
                    ->dateTime('M j, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('upcoming')
                    ->query(fn (Builder $query): Builder => $query->where('date', '>=', now()))
                    ->label('Upcoming Events')
                    ->default(),
                Filter::make('past')
                    ->query(fn (Builder $query): Builder => $query->where('date', '<', now()))
                    ->label('Past Events'),
                SelectFilter::make('categories')
                    ->relationship('categories', 'name')
                    ->multiple()
                    ->preload(),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('date', 'asc')
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
            'index' => Pages\ListEvents::route('/'),
            'create' => Pages\CreateEvents::route('/create'),
            'view' => Pages\ViewEvents::route('/{record}'),
            'edit' => Pages\EditEvents::route('/{record}/edit'),
        ];
    }
}
