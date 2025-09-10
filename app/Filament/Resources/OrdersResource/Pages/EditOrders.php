<?php

namespace App\Filament\Resources\OrdersResource\Pages;

use App\Filament\Resources\OrdersResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;

class EditOrders extends EditRecord
{
    protected static string $resource = OrdersResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
