<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum RefuelingType: string implements HasLabel
{
    case refueling = 'refueling';
    case filling = 'filling';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::refueling => 'Betankung',
            self::filling => 'Lieferung',
        };
    }
}
