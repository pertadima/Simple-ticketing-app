<?php

namespace App\Filament\Widgets;

use App\Models\Users;
use App\Models\Events;
use App\Models\Orders;
use App\Models\Tickets;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AdminStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Users', Users::count())
                ->description('Registered users')
                ->descriptionIcon('heroicon-m-users')
                ->color('success'),
            
            Stat::make('Active Events', Events::where('date', '>=', now())->count())
                ->description('Upcoming events')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('info'),
            
            Stat::make('Total Orders', Orders::count())
                ->description('All time orders')
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('warning'),
            
            Stat::make('Revenue', '$' . number_format(Orders::where('status', 'paid')->sum('total_amount'), 2))
                ->description('Total revenue')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success'),
        ];
    }
}
