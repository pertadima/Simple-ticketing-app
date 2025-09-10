<?php

namespace App\Filament\Resources\TicketCategoriesResource\Pages;

use App\Filament\Resources\TicketCategoriesResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions\DeleteAction;

class EditTicketCategories extends EditRecord
{
    protected static string $resource = TicketCategoriesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
