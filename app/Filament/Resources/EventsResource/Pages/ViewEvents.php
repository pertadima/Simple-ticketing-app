<?php

namespace App\Filament\Resources\EventsResource\Pages;

use App\Filament\Resources\EventsResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions\EditAction;

class ViewEvents extends ViewRecord
{
    protected static string $resource = EventsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
