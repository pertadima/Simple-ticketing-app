<?php

namespace App\Filament\Resources\EventCategoriesResource\Pages;

use App\Filament\Resources\EventCategoriesResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditEventCategories extends EditRecord
{
    protected static string $resource = EventCategoriesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
