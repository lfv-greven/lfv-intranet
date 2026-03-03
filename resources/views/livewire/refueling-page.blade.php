<div class="mx-auto flex max-w-5xl flex-col gap-8 px-6 py-12 lg:py-16">
    <div class="space-y-3">
        <h1 class="text-left text-3xl font-semibold text-neutral-900 lg:text-4xl">
            Tankstelle
        </h1>
        <p class="max-w-3xl text-neutral-600">
            Erfasse einen neuen Tankvorgang oder prüfe deine letzten Einträge.
        </p>
    </div>

    <div
        x-data="{
            activeTab: (new URLSearchParams(window.location.search).get('tab') === 'history') ? 'history' : 'create',
            setTab(tab) {
                this.activeTab = tab;
                const url = new URL(window.location.href);
                url.searchParams.set('tab', tab);
                window.history.replaceState({}, '', url);
            },
        }"
        class="space-y-6"
    >
        <div class="overflow-x-auto pb-1">
            <div class="inline-flex w-max items-center gap-1 rounded-2xl border border-neutral-200 bg-white p-1 shadow-sm whitespace-nowrap">
                <button
                    type="button"
                    class="rounded-xl px-4 py-2 text-sm font-semibold transition"
                    :class="activeTab === 'create' ? 'bg-neutral-900 text-white shadow-sm' : 'text-neutral-600 hover:text-neutral-900'"
                    x-on:click="setTab('create')"
                >
                    Tanken eintragen
                </button>
                <button
                    type="button"
                    class="rounded-xl px-4 py-2 text-sm font-semibold transition"
                    :class="activeTab === 'history' ? 'bg-neutral-900 text-white shadow-sm' : 'text-neutral-600 hover:text-neutral-900'"
                    x-on:click="setTab('history')"
                >
                    Meine Tankvorgänge
                </button>
            </div>
        </div>

        <div x-show="activeTab === 'create'" x-cloak>
            <form class="grid gap-6" wire:submit.prevent="save" x-on:focusin.once="window.trackUmamiEvent('refueling_start')">
                {{ $this->form }}

                <div class="pt-2">
                    <x-filament::button type="submit" class="w-full">
                        Betankung speichern
                    </x-filament::button>
                </div>
            </form>
        </div>

        <div x-show="activeTab === 'history'" x-cloak>
            <div class="overflow-hidden rounded-2xl border border-neutral-200 bg-white shadow-sm">
                <div class="border-b border-neutral-200 px-5 py-4">
                    <h2 class="text-base font-semibold text-neutral-900">Meine letzten Einträge</h2>
                </div>

                @if ($myRefuelings->isEmpty())
                    <div class="px-5 py-10 text-sm text-neutral-500">
                        Du hast bisher noch keine Tankvorgänge erfasst.
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-neutral-200 text-sm">
                            <thead class="bg-neutral-50">
                            <tr>
                                <th class="px-5 py-3 text-left font-semibold text-neutral-600">Datum</th>
                                <th class="px-5 py-3 text-left font-semibold text-neutral-600">Tankstelle</th>
                                <th class="px-5 py-3 text-left font-semibold text-neutral-600">Kennzeichen</th>
                                <th class="px-5 py-3 text-right font-semibold text-neutral-600">Menge</th>
                                <th class="px-5 py-3 text-right font-semibold text-neutral-600">Zählerstand</th>
                            </tr>
                            </thead>
                            <tbody class="divide-y divide-neutral-100 bg-white">
                            @php($lastYear = null)
                            @foreach ($myRefuelings as $entry)
                                @php($year = optional($entry->date)->format('Y') ?: 'Ohne Datum')
                                @if ($year !== $lastYear)
                                    <tr class="bg-neutral-100/70">
                                        <td colspan="5" class="px-5 py-2 text-xs font-semibold uppercase tracking-wide text-neutral-600">
                                            {{ $year }}
                                        </td>
                                    </tr>
                                    @php($lastYear = $year)
                                @endif
                                <tr class="hover:bg-neutral-50/70">
                                    @php($billingStatus = $entry->mayBeSold() ? ($entry->isExported() ? ['icon' => '✅', 'label' => 'Abgerechnet'] : ['icon' => '🕒', 'label' => 'Übermittelt (noch nicht abgerechnet)']) : null)
                                    <td class="whitespace-nowrap px-5 py-3 text-neutral-800">{{ optional($entry->date)->format('d.m.Y H:i') }}</td>
                                    <td class="whitespace-nowrap px-5 py-3 text-neutral-700">{{ $entry->gasStation?->name }}</td>
                                    <td class="whitespace-nowrap px-5 py-3 text-neutral-700">{{ $entry->buyer_registration ?: '–' }}</td>
                                    <td class="whitespace-nowrap px-5 py-3 text-right font-medium {{ $entry->amount < 0 ? 'text-red-600' : 'text-emerald-600' }}">
                                        <span class="inline-flex items-center justify-end gap-2">
                                            @if ($billingStatus)
                                                <x-ui.tooltip-icon :icon="$billingStatus['icon']" :label="$billingStatus['label']" />
                                            @endif
                                            <span>{{ number_format(abs($entry->amount), 0, ',', '.') }} l</span>
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-3 text-right text-neutral-700">{{ number_format($entry->counter_reading, 0, ',', '.') }} l</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="text-left">
        <a href="{{ route('home') }}" class="link" wire:navigate data-umami-event="refueling_back_clicked">
            zurück
        </a>
    </div>
</div>
