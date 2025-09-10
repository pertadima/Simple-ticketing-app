<?php

namespace App\Filament\Resources\TicketsResource\Pages;

use App\Filament\Resources\TicketsResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;

class EditTickets extends EditRecord
{
    protected static string $resource = TicketsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
