<?php

namespace App\Filament\Resources\GasStationResource\Pages;

use App\Filament\Resources\GasStationResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageGasStations extends ManageRecords
{
    protected static string $resource = GasStationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
