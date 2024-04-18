@props([
    'external' => false,
    'href',
    'icon_url',
])

<a
    href="{{ $href }}"
    class="flex bg-neutral-200 rounded items-center p-4 space-x-4"
    target="{{ $external ? '_blank' : '_self' }}"
    @unless($external) wire:navigate=false @endif
>
    <img src="{{ $icon_url }}" alt="Öl" class="h-16">
    <div class="flex-1 font-bold">{{ $slot }}</div>
    <x-filament::icon icon="heroicon-s-chevron-right" class="w-4 h-4" />
</a>
