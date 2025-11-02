@props([
    'external' => false,
    'href',
    'icon_url',
])

<a
    href="{{ $href }}"
    class="flex bg-white border border-gray-200 shadow hover:shadow-lg rounded items-center p-4 space-x-4 transition"
    target="{{ $external ? '_blank' : '_self' }}"
    @unless($external) wire:navigate=false @endif
>
    <img src="{{ $icon_url }}" alt="Öl" class="h-16">
    <div class="flex-1 font-bold">{{ $slot }}</div>
    <x-filament::icon icon="heroicon-s-chevron-right" class="w-4 h-4" />
</a>
