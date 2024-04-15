<?php

namespace App\Filament\Resources\RefuelingResource\Pages;

use App\Filament\Resources\RefuelingResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageRefuelings extends ManageRecords
{
    protected static string $resource = RefuelingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
