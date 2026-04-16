<?php

use Livewire\Attributes\Url;
use Livewire\Component;

new class extends Component
{
    #[Url(as: 'tab', except: 'create', history: true)]
    public string $tab = 'create';
};
?>

<div class="mx-auto flex max-w-5xl flex-col gap-8 px-6 py-12 lg:py-16">
    <div class="space-y-3">
        <h1 class="text-left text-3xl font-semibold text-neutral-900 lg:text-4xl">
            Tankstelle
        </h1>
        <p class="max-w-3xl text-neutral-600">
            Erfasse einen neuen Tankvorgang oder prüfe deine letzten Einträge.
        </p>
    </div>

    <div x-data class="space-y-6">
        <div class="overflow-x-auto pb-1">
            <div class="inline-flex w-max items-center gap-1 rounded-2xl border border-neutral-200 bg-white p-1 shadow-sm whitespace-nowrap">
                <button
                    type="button"
                    class="rounded-xl px-4 py-2 text-sm font-semibold transition"
                    :class="$wire.tab === 'create' ? 'bg-neutral-900 text-white shadow-sm' : 'text-neutral-600 hover:text-neutral-900'"
                    wire:click="$set('tab', 'create')"
                >
                    Tanken eintragen
                </button>
                <button
                    type="button"
                    class="rounded-xl px-4 py-2 text-sm font-semibold transition"
                    :class="$wire.tab === 'history' ? 'bg-neutral-900 text-white shadow-sm' : 'text-neutral-600 hover:text-neutral-900'"
                    wire:click="$set('tab', 'history')"
                >
                    Meine Tankvorgänge
                </button>
                <button
                    type="button"
                    class="rounded-xl px-4 py-2 text-sm font-semibold transition"
                    :class="$wire.tab === 'prices' ? 'bg-neutral-900 text-white shadow-sm' : 'text-neutral-600 hover:text-neutral-900'"
                    wire:click="$set('tab', 'prices')"
                >
                    Preise
                </button>
            </div>
        </div>

        <div x-show="$wire.tab === 'create'" x-cloak>
            <livewire:refueling-entry-page />
        </div>

        <template x-if="$wire.tab === 'history'">
            <div x-cloak>
                <livewire:refueling-history-page />
            </div>
        </template>

        <template x-if="$wire.tab === 'prices'">
            <div x-cloak>
                <livewire:refueling-prices-page />
            </div>
        </template>
    </div>

    <div class="text-left">
        <a href="{{ route('home') }}" class="link" wire:navigate data-umami-event="refueling_back_clicked">
            zurück
        </a>
    </div>
</div>
