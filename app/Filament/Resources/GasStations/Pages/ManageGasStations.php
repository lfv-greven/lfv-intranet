<?php

namespace App\Filament\Resources\GasStations\Pages;

use App\Filament\Resources\GasStations\GasStationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageGasStations extends ManageRecords
{
    protected static string $resource = GasStationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
