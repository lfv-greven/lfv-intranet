<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum FiSettlementStatus: string implements HasColor, HasLabel
{
    case DRAFT = 'draft';
    case QUEUED = 'queued';
    case PROCESSING = 'processing';
    case COMPLETED = 'completed';
    case FAILED = 'failed';

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::DRAFT => 'gray',
            self::QUEUED => 'gray',
            self::PROCESSING => 'info',
            self::COMPLETED => 'success',
            self::FAILED => 'danger',
        };
    }

    public function getLabel(): ?string
    {
        return match ($this) {
            self::DRAFT => 'Entwurf',
            self::QUEUED => 'Warteschlange',
            self::PROCESSING => 'In Arbeit',
            self::COMPLETED => 'Abgeschlossen',
            self::FAILED => 'Fehlgeschlagen',
        };
    }
}
