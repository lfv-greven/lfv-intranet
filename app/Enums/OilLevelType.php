<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum OilLevelType: string implements HasLabel
{
    case absolute = 'absolute';
    case relative = 'relative';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::absolute => 'Absolut',
            self::relative => 'Relativ',
        };
    }
}
