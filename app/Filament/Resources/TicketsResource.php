<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TicketsResource\Pages;
use App\Models\Tickets;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

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
                Forms\Components\Section::make('Ticket Details')
                    ->schema([
                        Forms\Components\Select::make('event_id')
                            ->relationship('event', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('category_id')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('type_id')
                            ->relationship('type', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                    ])
                    ->columns(3),
                
                Forms\Components\Section::make('Pricing & Availability')
                    ->schema([
                        Forms\Components\TextInput::make('price')
                            ->numeric()
                            ->prefix('$')
                            ->required()
                            ->step(0.01),
                        Forms\Components\TextInput::make('quota')
                            ->numeric()
                            ->required()
                            ->minValue(1),
                        Forms\Components\TextInput::make('sold_count')
                            ->numeric()
                            ->default(0)
                            ->minValue(0),
                        Forms\Components\Toggle::make('requires_id_verification')
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
                Tables\Columns\TextColumn::make('ticket_id')
                    ->label('ID')
                    ->sortable(),
                Tables\Columns\TextColumn::make('event.name')
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->weight('medium'),
                Tables\Columns\TextColumn::make('category.name')
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('type.name')
                    ->badge()
                    ->color('success'),
                Tables\Columns\TextColumn::make('price')
                    ->money('USD')
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('availability')
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
                Tables\Columns\IconColumn::make('requires_id_verification')
                    ->boolean()
                    ->label('ID Required'),
                Tables\Columns\TextColumn::make('orderDetails_count')
                    ->counts('orderDetails')
                    ->label('Orders')
                    ->badge()
                    ->color('primary'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('event')
                    ->relationship('event', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('category')
                    ->relationship('category', 'name')
                    ->multiple(),
                Tables\Filters\Filter::make('available')
                    ->query(fn (Builder $query): Builder => $query->whereRaw('quota > sold_count'))
                    ->label('Available Tickets'),
                Tables\Filters\Filter::make('sold_out')
                    ->query(fn (Builder $query): Builder => $query->whereRaw('quota <= sold_count'))
                    ->label('Sold Out'),
                Tables\Filters\Filter::make('id_required')
                    ->query(fn (Builder $query): Builder => $query->where('requires_id_verification', true))
                    ->label('ID Verification Required'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
