<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RoleResource\Pages;
use App\Filament\Resources\RoleResource\RelationManagers;
use App\Models\Role;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\CheckboxList;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BooleanColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\Filter;

class RoleResource extends Resource
{
    protected static ?string $model = Role::class;

    protected static ?string $navigationIcon = 'heroicon-o-key';
    
    protected static ?string $navigationGroup = 'Admin Management';
    
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Role Information')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->helperText('Internal role name (lowercase, underscores allowed)')
                            ->columnSpan(1),
                        
                        TextInput::make('display_name')
                            ->required()
                            ->maxLength(255)
                            ->helperText('Human-readable role name')
                            ->columnSpan(1),
                        
                        Textarea::make('description')
                            ->rows(3)
                            ->columnSpanFull(),
                        
                        Toggle::make('is_active')
                            ->label('Is Active')
                            ->default(true)
                            ->columnSpan(1),
                    ])
                    ->columns(2),
                    
                Section::make('Permissions')
                    ->schema([
                        CheckboxList::make('permissions')
                            ->label('Assign Permissions')
                            ->options([
                                'users.view' => 'View API Users',
                                'users.create' => 'Create API Users',
                                'users.edit' => 'Edit API Users',
                                'users.delete' => 'Delete API Users',
                                
                                'events.view' => 'View Events',
                                'events.create' => 'Create Events',
                                'events.edit' => 'Edit Events',
                                'events.delete' => 'Delete Events',
                                
                                'orders.view' => 'View Orders',
                                'orders.create' => 'Create Orders',
                                'orders.edit' => 'Edit Orders',
                                'orders.delete' => 'Delete Orders',
                                
                                'tickets.view' => 'View Tickets',
                                'tickets.create' => 'Create Tickets',
                                'tickets.edit' => 'Edit Tickets',
                                'tickets.delete' => 'Delete Tickets',
                                
                                'categories.view' => 'View Categories',
                                'categories.create' => 'Create Categories',
                                'categories.edit' => 'Edit Categories',
                                'categories.delete' => 'Delete Categories',
                                
                                'roles.view' => 'View Roles',
                                'roles.create' => 'Create Roles',
                                'roles.edit' => 'Edit Roles',
                                'roles.delete' => 'Delete Roles',
                                
                                'admin_users.view' => 'View Admin Users',
                                'admin_users.create' => 'Create Admin Users',
                                'admin_users.edit' => 'Edit Admin Users',
                                'admin_users.delete' => 'Delete Admin Users',
                                
                                'dashboard.view' => 'View Dashboard',
                                'system.manage' => 'Manage System',
                            ])
                            ->columns(3)
                            ->gridDirection('row')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('role_id')
                    ->label('ID')
                    ->sortable()
                    ->searchable(),
                    
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),
                    
                TextColumn::make('display_name')
                    ->searchable()
                    ->sortable(),
                    
                TextColumn::make('description')
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        
                        if (strlen($state) <= 50) {
                            return null;
                        }
                        
                        return $state;
                    }),
                    
                BadgeColumn::make('admin_users_count')
                    ->counts('adminUsers')
                    ->label('Users Count')
                    ->color('success'),
                    
                BooleanColumn::make('is_active')
                    ->label('Active')
                    ->sortable(),
                    
                TextColumn::make('created_at')
                    ->dateTime('M j, Y')
                    ->sortable()
                    ->label('Created'),
            ])
            ->filters([
                Filter::make('active')
                    ->query(fn (Builder $query): Builder => $query->where('is_active', true))
                    ->label('Active Only'),
                    
                Filter::make('inactive')
                    ->query(fn (Builder $query): Builder => $query->where('is_active', false))
                    ->label('Inactive Only'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation(),
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
            'index' => Pages\ListRoles::route('/'),
            'create' => Pages\CreateRole::route('/create'),
            'edit' => Pages\EditRole::route('/{record}/edit'),
        ];
    }
}
