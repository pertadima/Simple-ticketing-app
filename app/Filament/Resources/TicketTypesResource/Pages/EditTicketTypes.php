<?php

namespace App\Filament\Resources\TicketTypesResource\Pages;

use App\Filament\Resources\TicketTypesResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions\DeleteAction;

class EditTicketTypes extends EditRecord
{
    protected static string $resource = TicketTypesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
