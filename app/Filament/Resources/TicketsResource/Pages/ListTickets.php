<?php

namespace App\Filament\Resources\TicketsResource\Pages;

use App\Filament\Resources\TicketsResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\CreateAction;

class ListTickets extends ListRecords
{
    protected static string $resource = TicketsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
