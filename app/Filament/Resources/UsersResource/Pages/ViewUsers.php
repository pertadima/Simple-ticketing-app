<?php

namespace App\Filament\Resources\UsersResource\Pages;

use App\Filament\Resources\UsersResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions\EditAction;

class ViewUsers extends ViewRecord
{
    protected static string $resource = UsersResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
