<?php

use App\Services\VereinsfliegerFuelPrices;
use Livewire\Component;

new class extends Component
{
    /**
     * @return array<int, array{fuel: string, amount: string, vat_rate: string|null}>
     */
    public function getPriceBoardEntriesProperty(): array
    {
        return app(VereinsfliegerFuelPrices::class)->getCurrentPriceBoardEntries();
    }

    public function getPriceBoardStandProperty(): string
    {
        return now()->format('d.m.Y');
    }

    /**
     * @return array<string, int>
     */
    public function getVatRateMarkersProperty(): array
    {
        return collect($this->priceBoardEntries)
            ->pluck('vat_rate')
            ->filter(fn (mixed $rate) => filled($rate))
            ->unique()
            ->values()
            ->mapWithKeys(fn (string $rate, int $index) => [$rate => $index + 1])
            ->all();
    }
};
?>

<div class="space-y-3">
    <x-ui.card title="Aktuelle Preise">
        <div class="bg-[#fffaf4] px-5 py-5 sm:px-6 sm:py-6">
            @forelse ($this->priceBoardEntries as $entry)
                @php($vatMarker = data_get($this->vatRateMarkers, data_get($entry, 'vat_rate')))

                <div class="grid grid-cols-[minmax(0,1fr)_auto] items-end gap-4 border-b border-primary/15 py-4 first:pt-0 last:border-b-0 last:pb-0 sm:py-5">
                    <div class="min-w-0">
                        <p class="text-[11px] font-medium uppercase tracking-[0.1em] text-neutral-500">Kraftstoff</p>
                        <p class="mt-1 truncate text-xl font-semibold tracking-tight text-neutral-900 sm:text-2xl">
                            {{ strtoupper($entry['fuel']) }}
                        </p>
                    </div>

                    <div class="flex items-end gap-1.5">
                        <p class="font-mono text-4xl leading-none font-bold text-neutral-900 tabular-nums sm:text-5xl">
                            {{ $entry['amount'] }}
                        </p>
                        <span class="mb-1.5 text-xs font-semibold uppercase tracking-[0.08em] text-primary sm:text-sm">
                            Euro
                            @if (filled($vatMarker))
                                <sup class="align-top text-[9px] leading-none sm:text-[10px]">{{ $vatMarker }}</sup>
                            @endif
                        </span>
                    </div>
                </div>
            @empty
                <div class="py-5 text-center text-sm text-neutral-500 sm:py-6">
                    Keine Preise
                </div>
            @endforelse
        </div>
    </x-ui.card>

    <div class="flex items-end justify-between gap-4 text-[11px] text-neutral-500 sm:text-xs">
        <div class="min-w-0 space-y-0.5 text-left">
            @foreach ($this->vatRateMarkers as $rate => $marker)
                <p>{{ $marker }}) Inkl. {{ $rate }} % MwSt.</p>
            @endforeach
        </div>

        <div class="shrink-0 text-right">
            Stand {{ $this->priceBoardStand }}
        </div>
    </div>
</div>
