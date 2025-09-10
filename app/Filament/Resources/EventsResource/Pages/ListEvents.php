<?php

namespace App\Filament\Resources\EventsResource\Pages;

use App\Filament\Resources\EventsResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\CreateAction;

class ListEvents extends ListRecords
{
    protected static string $resource = EventsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
