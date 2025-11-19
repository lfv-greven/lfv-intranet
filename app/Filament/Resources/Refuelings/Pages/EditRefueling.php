<?php

namespace App\Filament\Resources\Refuelings\Pages;

use App\Filament\Resources\Refuelings\RefuelingResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditRefueling extends EditRecord
{
    protected static string $resource = RefuelingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
