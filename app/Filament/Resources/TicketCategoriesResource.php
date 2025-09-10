<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TicketCategoriesResource\Pages;
use App\Models\TicketCategories;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;

class TicketCategoriesResource extends Resource
{
    protected static ?string $model = TicketCategories::class;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?string $navigationLabel = 'Ticket Categories';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Category Information')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
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
                TextColumn::make('created_at')
                    ->dateTime('M j, Y')
                    ->sortable(),
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
            ->paginated([25, 50, 100]);
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
            'index' => Pages\ListTicketCategories::route('/'),
            'create' => Pages\CreateTicketCategories::route('/create'),
            'edit' => Pages\EditTicketCategories::route('/{record}/edit'),
        ];
    }
}
