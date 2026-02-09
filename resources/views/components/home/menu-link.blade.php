@props([
    'external' => false,
    'href',
    'icon_url',
])

<a
    href="{{ $href }}"
    class="group relative flex items-center gap-4 overflow-hidden rounded-2xl border border-white/60 bg-white/80 p-5 shadow-[0_18px_50px_rgba(15,23,42,0.08)] transition duration-300 hover:-translate-y-0.5 hover:border-white hover:shadow-[0_28px_70px_rgba(15,23,42,0.18)]"
    target="{{ $external ? '_blank' : '_self' }}"
    @unless($external) wire:navigate=false @endif
>
    <div class="absolute -right-10 -top-10 h-24 w-24 rounded-full bg-[#f65812]/15 blur-2xl transition group-hover:bg-[#f65812]/25"></div>
    <div class="grid h-14 w-14 place-items-center rounded-2xl bg-[#f3ede2] ring-1 ring-white/70">
        <img src="{{ $icon_url }}" alt="" class="h-10 w-10">
    </div>
    <div class="flex-1 text-lg font-semibold text-neutral-900">{{ $slot }}</div>
    <x-filament::icon icon="heroicon-s-chevron-right" class="h-4 w-4 text-neutral-400 transition group-hover:translate-x-0.5 group-hover:text-neutral-600" />
</a>
