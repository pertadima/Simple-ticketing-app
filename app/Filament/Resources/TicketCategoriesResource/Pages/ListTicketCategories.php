<?php

namespace App\Filament\Resources\TicketCategoriesResource\Pages;

use App\Filament\Resources\TicketCategoriesResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\CreateAction;

class ListTicketCategories extends ListRecords
{
    protected static string $resource = TicketCategoriesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
