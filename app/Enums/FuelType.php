<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum FuelType: string implements HasLabel
{
    case avgas = 'avgas';
    case super = 'super';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::avgas => 'AVGAS',
            self::super => 'Super',
        };
    }
}
