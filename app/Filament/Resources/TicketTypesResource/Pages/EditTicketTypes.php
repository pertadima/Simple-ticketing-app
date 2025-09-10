<?php

namespace App\Filament\Resources\TicketTypesResource\Pages;

use App\Filament\Resources\TicketTypesResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

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
