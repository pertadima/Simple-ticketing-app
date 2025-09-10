<?php

namespace App\Filament\Resources;

use App\Models\Tickets;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use App\Filament\Resources\TicketsResource\Pages;
use Filament\Resources\Resource;
use Filament\Forms;
use Filament\Tables;

class TicketsResource extends Resource
{
    protected static ?string $model = Tickets::class;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';

    protected static ?string $navigationGroup = 'Event Management';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Ticket Details')
                    ->schema([
                        Select::make('event_id')
                            ->relationship('event', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('category_id')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('type_id')
                            ->relationship('type', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                    ])
                    ->columns(3),
                
                Section::make('Pricing & Availability')
                    ->schema([
                        TextInput::make('price')
                            ->numeric()
                            ->prefix('$')
                            ->required()
                            ->step(0.01),
                        TextInput::make('quota')
                            ->numeric()
                            ->required()
                            ->minValue(1),
                        TextInput::make('sold_count')
                            ->numeric()
                            ->default(0)
                            ->minValue(0),
                        Toggle::make('requires_id_verification')
                            ->label('Requires ID Verification')
                            ->default(false),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('ticket_id')
                    ->label('ID')
                    ->sortable(),
                TextColumn::make('event.name')
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->weight('medium'),
                TextColumn::make('category.name')
                    ->badge()
                    ->color('info'),
                TextColumn::make('type.name')
                    ->badge()
                    ->color('success'),
                TextColumn::make('price')
                    ->money('USD')
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('availability')
                    ->getStateUsing(fn ($record) => ($record->quota - $record->sold_count) . ' / ' . $record->quota)
                    ->label('Available / Total')
                    ->badge()
                    ->color(function ($record) {
                        $available = $record->quota - $record->sold_count;
                        $percentage = $available / $record->quota;
                        
                        if ($percentage > 0.5) return 'success';
                        if ($percentage > 0.2) return 'warning';
                        return 'danger';
                    }),
                IconColumn::make('requires_id_verification')
                    ->boolean()
                    ->label('ID Required'),
                TextColumn::make('orderDetails_count')
                    ->counts('orderDetails')
                    ->label('Orders')
                    ->badge()
                    ->color('primary'),
            ])
            ->filters([
                SelectFilter::make('event')
                    ->relationship('event', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('category')
                    ->relationship('category', 'name')
                    ->multiple(),
                Filter::make('available')
                    ->query(fn (Builder $query): Builder => $query->whereRaw('quota > sold_count'))
                    ->label('Available Tickets'),
                Filter::make('sold_out')
                    ->query(fn (Builder $query): Builder => $query->whereRaw('quota <= sold_count'))
                    ->label('Sold Out'),
                Filter::make('id_required')
                    ->query(fn (Builder $query): Builder => $query->where('requires_id_verification', true))
                    ->label('ID Verification Required'),
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
            ->defaultSort('created_at', 'desc')
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
            'index' => Pages\ListTickets::route('/'),
            'create' => Pages\CreateTickets::route('/create'),
            'view' => Pages\ViewTickets::route('/{record}'),
            'edit' => Pages\EditTickets::route('/{record}/edit'),
        ];
    }
}
