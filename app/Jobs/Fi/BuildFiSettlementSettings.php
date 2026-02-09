<?php

namespace App\Jobs\Fi;

use Illuminate\Support\Arr;

class BuildFiSettlementSettings
{
    public static function normalize(array $settings): array
    {
        $ftidFilter = data_get($settings, 'ftid_filter', [8]);

        return [
            'ftid_filter' => self::normalizeFtidFilter($ftidFilter),
        ];
    }

    private static function normalizeFtidFilter(mixed $ftidFilter): array
    {
        return collect(Arr::wrap($ftidFilter))
            ->map(fn ($value) => (int) $value)
            ->filter()
            ->values()
            ->all() ?: [8];
    }
}
