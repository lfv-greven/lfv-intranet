<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum TrainingFundReportStatus: string implements HasColor, HasLabel
{
    case PENDING = 'pending';
    case QUEUED = 'queued';
    case PROCESSING = 'processing';
    case COMPLETED = 'completed';
    case FAILED = 'failed';

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::PENDING => 'gray',
            self::QUEUED => 'warning',
            self::PROCESSING => 'info',
            self::COMPLETED => 'success',
            self::FAILED => 'danger',
        };
    }

    public function getLabel(): ?string
    {
        return match ($this) {
            self::PENDING => 'Ausstehend',
            self::QUEUED => 'Geplant',
            self::PROCESSING => 'Berechne...',
            self::COMPLETED => 'Vollständig',
            self::FAILED => 'Fehlgeschlagen',
        };
    }
}
