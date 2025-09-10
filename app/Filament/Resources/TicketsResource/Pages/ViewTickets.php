<?php

namespace App\Filament\Resources\TicketsResource\Pages;

use App\Filament\Resources\TicketsResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions\EditAction;

class ViewTickets extends ViewRecord
{
    protected static string $resource = TicketsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
