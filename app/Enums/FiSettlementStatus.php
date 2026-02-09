<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum FiSettlementStatus: string implements HasLabel
{
    case DRAFT = 'draft';
    case QUEUED = 'queued';
    case PROCESSING = 'processing';
    case COMPLETED = 'completed';
    case FAILED = 'failed';

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
