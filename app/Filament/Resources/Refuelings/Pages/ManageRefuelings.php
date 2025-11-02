<?php

namespace App\Filament\Resources\Refuelings\Pages;

use App\Filament\Resources\Refuelings\RefuelingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageRefuelings extends ManageRecords
{
    protected static string $resource = RefuelingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
