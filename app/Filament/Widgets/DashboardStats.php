<?php

namespace App\Filament\Widgets;

use App\Models\GasStation;
use App\Models\MotortimeReminder;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Flowframe\Trend\Trend;

class DashboardStats extends BaseWidget
{
    protected function getStats(): array
    {
        $motortimeReminders = Trend::model(MotortimeReminder::class)
            ->between(now()->subMonth(), now())
            ->perDay()
            ->count()
            ->pluck('aggregate')
            ->toArray();
        $motortimeRemindersLast24h = MotortimeReminder::where('created_at', '>=', now()->subHours(24))
            ->count();

        return [
            ...GasStation::all()->map(fn (GasStation $gasStation) => Stat::make(
                $gasStation->name,
                $gasStation->refuelings()->sum('amount').' l',
            )
                ->description('Letzte 7 Tage: '.abs($gasStation->refuelings()->refueling()->where('date', '>', today()->subDays(7))->sum('amount')).' l')
            ),
            Stat::make('Fehler Flugerfassung', $motortimeRemindersLast24h)
                ->description('Letzte 24 Stunden.')
                ->chart($motortimeReminders),
        ];
    }
}
