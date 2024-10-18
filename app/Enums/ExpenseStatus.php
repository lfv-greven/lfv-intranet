<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum ExpenseStatus: string implements HasLabel
{
    case OPEN = 'open';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::OPEN => 'Offen',
            self::APPROVED => 'Geprüft',
            self::REJECTED => 'Verworfen',
        };
    }
}
