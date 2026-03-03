<?php

use App\Models\Refueling;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Component;

new class extends Component
{
    public function getMyRefuelingsProperty(): Collection
    {
        return Refueling::query()
            ->with(['gasStation', 'aircraft'])
            ->where('user_id', auth()->id())
            ->orderByDesc('date')
            ->limit(50)
            ->get();
    }
};
?>

<div class="overflow-hidden rounded-2xl border border-neutral-200 bg-white shadow-sm">
    <div class="border-b border-neutral-200 px-5 py-4">
        <h2 class="text-base font-semibold text-neutral-900">Meine letzten Einträge</h2>
    </div>

    @if ($this->myRefuelings->isEmpty())
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
                @foreach ($this->myRefuelings as $entry)
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
