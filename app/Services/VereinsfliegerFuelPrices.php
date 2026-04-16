<?php

namespace App\Services;

use App\Models\GasStation;
use Carbon\CarbonImmutable;

class VereinsfliegerFuelPrices
{
    private const CACHE_KEY_PREFIX = 'vf:articles:';

    private const FAILURE_CACHE_MINUTES = 10;

    /**
     * @return array<int, array{fuel: string, amount: string, vat_rate: string|null}>
     */
    public function getCurrentPriceBoardEntries(): array
    {
        $articles = $this->getArticlesById();

        return GasStation::query()
            ->whereNotNull('vf_articleid')
            ->where('vf_articleid', '!=', '')
            ->orderBy('name')
            ->get()
            ->map(function (GasStation $station) use ($articles): ?array {
                $article = data_get($articles, $station->vf_articleid);
                if (! is_array($article)) {
                    return null;
                }

                $price = $this->findCurrentPrice(data_get($article, 'prices', []));
                if ($price === null) {
                    return null;
                }

                return [
                    'fuel' => $station->name,
                    'amount' => number_format($price['unit_price'], 2, ',', ''),
                    'vat_rate' => $price['sales_tax'],
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function getArticlesById(): array
    {
        $cacheKey = self::CACHE_KEY_PREFIX.now()->toDateString();
        $cached = cache()->get($cacheKey);

        if ($this->isValidCachedArticleMap($cached)) {
            return $cached;
        }

        if ($cached !== null) {
            cache()->forget($cacheKey);
        }

        $articlesById = $this->fetchArticlesById();

        if ($articlesById === []) {
            cache()->put($cacheKey, $articlesById, now()->addMinutes(self::FAILURE_CACHE_MINUTES));

            return [];
        }

        cache()->put($cacheKey, $articlesById, now()->endOfDay());

        return $articlesById;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function fetchArticlesById(): array
    {
        $client = app(VereinsfliegerClient::class);
        [$success, $status, $response] = $client->callWithRetry(fn ($vf) => $vf->GetArticles());

        if (! $success || $status !== 200 || ! is_array($response)) {
            return [];
        }

        $articlesById = [];

        foreach ($response as $article) {
            if (! is_array($article)) {
                continue;
            }

            $articleId = (string) data_get($article, 'articleid');
            if (blank($articleId)) {
                continue;
            }

            $articlesById[$articleId] = $article;
        }

        return $articlesById;
    }

    /**
     * @return array{unit_price: float, sales_tax: string|null}|null
     */
    private function findCurrentPrice(mixed $prices): ?array
    {
        if (! is_array($prices)) {
            return null;
        }

        $today = CarbonImmutable::today();
        $current = collect($prices)
            ->filter(fn ($price) => is_array($price))
            ->map(function (array $price): ?array {
                $validFrom = $this->parseDate(data_get($price, 'validfrom'));
                $validTo = $this->parseDate(data_get($price, 'validto'));
                $unitPrice = data_get($price, 'unitprice');

                if (! $validFrom || ! $validTo || ! is_numeric($unitPrice)) {
                    return null;
                }

                $salesTax = $this->normalizeSalesTax(data_get($price, 'salestax'));

                return [
                    'valid_from' => $validFrom,
                    'valid_to' => $validTo,
                    'unit_price' => (float) $unitPrice,
                    'sales_tax' => $salesTax,
                ];
            })
            ->filter();

        $activePrice = $current
            ->filter(fn (array $price) => $price['valid_from']->lte($today) && $price['valid_to']->gte($today))
            ->sortByDesc(fn (array $price) => $price['valid_from']->timestamp)
            ->first();

        if (is_array($activePrice)) {
            return [
                'unit_price' => $activePrice['unit_price'],
                'sales_tax' => $activePrice['sales_tax'],
            ];
        }

        $latestPrice = $current
            ->sortByDesc(fn (array $price) => $price['valid_from']->timestamp)
            ->first();

        if (! is_array($latestPrice)) {
            return null;
        }

        return [
            'unit_price' => $latestPrice['unit_price'],
            'sales_tax' => $latestPrice['sales_tax'],
        ];
    }

    private function parseDate(mixed $value): ?CarbonImmutable
    {
        if (! is_string($value) || blank($value)) {
            return null;
        }

        try {
            return CarbonImmutable::parse($value)->startOfDay();
        } catch (\Throwable) {
            return null;
        }
    }

    private function normalizeSalesTax(mixed $value): ?string
    {
        if (! is_numeric($value)) {
            return null;
        }

        $normalized = rtrim(rtrim(number_format((float) $value, 2, '.', ''), '0'), '.');

        return str_replace('.', ',', $normalized);
    }

    private function isValidCachedArticleMap(mixed $cached): bool
    {
        if (! is_array($cached)) {
            return false;
        }

        foreach ($cached as $articleId => $article) {
            if (! is_string($articleId) || ! is_array($article)) {
                return false;
            }
        }

        return true;
    }
}
