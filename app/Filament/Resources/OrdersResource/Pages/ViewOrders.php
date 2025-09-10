<?php

namespace App\Filament\Resources\OrdersResource\Pages;

use App\Filament\Resources\OrdersResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions\EditAction;

class ViewOrders extends ViewRecord
{
    protected static string $resource = OrdersResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
