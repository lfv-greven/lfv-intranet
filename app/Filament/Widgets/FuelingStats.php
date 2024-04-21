<?php

namespace App\Filament\Widgets;

use App\Models\GasStation;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class FuelingStats extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            ...GasStation::all()->map(fn (GasStation $gasStation) => Stat::make(
                $gasStation->name,
                $gasStation->refuelings()->sum('amount').' l',
            )
                ->description('Letzte 7 Tage: '.abs($gasStation->refuelings()->refueling()->where('date', '>', today()->subDays(7))->sum('amount')).' l')
            ),
        ];
    }
}
