<?php

namespace App\Filament\Resources\EventCategoriesResource\Pages;

use App\Filament\Resources\EventCategoriesResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\CreateAction;

class ListEventCategories extends ListRecords
{
    protected static string $resource = EventCategoriesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
