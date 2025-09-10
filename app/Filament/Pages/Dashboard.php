<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\AdminStatsOverview;
use App\Filament\Widgets\OrdersChart;
use App\Filament\Widgets\RecentOrdersTable;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static string $view = 'filament.pages.dashboard';

    public function getWidgets(): array
    {
        return [
            AdminStatsOverview::class,
            OrdersChart::class,
            RecentOrdersTable::class,
        ];
    }

    public function getColumns(): int | array
    {
        return 2;
    }
}
