<?php

namespace App\Filament\Resources\EventsResource\Pages;

use App\Filament\Resources\EventsResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;

class EditEvents extends EditRecord
{
    protected static string $resource = EventsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
