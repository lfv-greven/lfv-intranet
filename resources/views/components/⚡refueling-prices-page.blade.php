<?php

use App\Services\VereinsfliegerFuelPrices;
use Livewire\Component;

new class extends Component
{
    /**
     * @return array<int, array{fuel: string, amount: string}>
     */
    public function getPriceBoardEntriesProperty(): array
    {
        return app(VereinsfliegerFuelPrices::class)->getCurrentPriceBoardEntries();
    }

    public function getPriceBoardStandProperty(): string
    {
        return now()->format('d.m.Y');
    }
};
?>

<div class="space-y-3">
    <x-ui.card title="Aktuelle Preise">
        <div class="bg-[#fffaf4] px-5 py-5 sm:px-6 sm:py-6">
            @forelse ($this->priceBoardEntries as $entry)
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

    <div class="text-right text-[11px] text-neutral-500 sm:text-xs">
        Stand {{ $this->priceBoardStand }}
    </div>
</div>
