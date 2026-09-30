<?php

namespace App\Filament\Widgets;

use App\Models\Lead;
use App\Models\Property;
use App\Models\Project;
use App\Models\Agent;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Properties', Property::count())
                ->description('Active: ' . Property::active()->count())
                ->descriptionIcon('heroicon-m-building-office-2')
                ->color('primary'),

            Stat::make('Total Projects', Project::count())
                ->description('Featured: ' . Project::featured()->count())
                ->descriptionIcon('heroicon-m-building-library')
                ->color('success'),

            Stat::make('New Leads', Lead::where('status', 'new')->count())
                ->description('Total leads: ' . Lead::count())
                ->descriptionIcon('heroicon-m-users')
                ->color('warning'),

            Stat::make('Active Agents', Agent::active()->count())
                ->descriptionIcon('heroicon-m-user-circle')
                ->color('info'),
        ];
    }
}
