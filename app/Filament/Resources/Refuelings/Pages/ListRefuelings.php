<?php

namespace App\Filament\Resources\Refuelings\Pages;

use App\Filament\Resources\Refuelings\RefuelingResource;
use App\Models\GasStation;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListRefuelings extends ListRecords
{
    protected static string $resource = RefuelingResource::class;

    public function getTabs(): array
    {
        return GasStation::all()->mapWithKeys(fn (GasStation $gasStation, $i) => [
            $gasStation->id => Tab::make()
                ->label($gasStation->name)
                ->modifyQueryUsing(fn (Builder $query) => $query->where('gas_station_id', $gasStation->id)),
        ])->toArray();
    }

    public function getDefaultActiveTab(): string|int|null
    {
        return GasStation::first()->id;
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
