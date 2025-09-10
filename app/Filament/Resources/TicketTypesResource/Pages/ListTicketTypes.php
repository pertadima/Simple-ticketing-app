<?php

namespace App\Filament\Resources\TicketTypesResource\Pages;

use App\Filament\Resources\TicketTypesResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\CreateAction;

class ListTicketTypes extends ListRecords
{
    protected static string $resource = TicketTypesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
