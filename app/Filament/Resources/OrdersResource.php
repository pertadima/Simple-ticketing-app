<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrdersResource\Pages;
use App\Models\Orders;
use App\Enums\OrderStatus;
use App\Enums\IdCardType;
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
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;

class OrdersResource extends Resource
{
    protected static ?string $model = Orders::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?string $navigationGroup = 'Order Management';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Order Information')
                    ->schema([
                        Select::make('user_id')
                            ->relationship('user', 'full_name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('total_amount')
                            ->numeric()
                            ->prefix('$')
                            ->required(),
                        TextInput::make('discount_amount')
                            ->numeric()
                            ->prefix('$')
                            ->default(0),
                        Select::make('status')
                            ->options(OrderStatus::class)
                            ->required()
                            ->default(OrderStatus::PENDING),
                    ])
                    ->columns(2),
                
                Section::make('Customer Details')
                    ->schema([
                        Select::make('id_card_type')
                            ->options(IdCardType::class)
                            ->required(),
                        TextInput::make('id_card_number')
                            ->required()
                            ->maxLength(255),
                        Toggle::make('email_verified')
                            ->default(false),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order_id')
                    ->label('Order ID')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('user.full_name')
                    ->searchable()
                    ->sortable()
                    ->label('Customer'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'paid' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('total_amount')
                    ->money('USD')
                    ->sortable()
                    ->weight('medium'),
                TextColumn::make('discount_amount')
                    ->money('USD')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('orderDetails_count')
                    ->counts('orderDetails')
                    ->label('Items')
                    ->badge()
                    ->color('info'),
                IconColumn::make('email_verified')
                    ->boolean()
                    ->label('Verified'),
                TextColumn::make('created_at')
                    ->dateTime('M j, Y g:i A')
                    ->sortable()
                    ->label('Order Date'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(OrderStatus::class)
                    ->multiple(),
                Filter::make('verified_only')
                    ->query(fn (Builder $query): Builder => $query->where('email_verified', true))
                    ->label('Email Verified'),
                Filter::make('high_value')
                    ->query(fn (Builder $query): Builder => $query->where('total_amount', '>=', 100))
                    ->label('High Value (≥$100)'),
                Filter::make('recent')
                    ->query(fn (Builder $query): Builder => $query->where('created_at', '>=', now()->subWeek()))
                    ->label('Recent (7 days)'),
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
            ->paginated([10, 25, 50, 100]);
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
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrders::route('/create'),
            'view' => Pages\ViewOrders::route('/{record}'),
            'edit' => Pages\EditOrders::route('/{record}/edit'),
        ];
    }
}
