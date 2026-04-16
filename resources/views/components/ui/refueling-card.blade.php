@props(['title' => null])

<div {{ $attributes->class('overflow-hidden rounded-2xl border border-neutral-200 bg-white shadow-sm') }}>
    @if (filled($title) || isset($header))
        <div class="border-b border-neutral-200 px-5 py-4">
            @isset($header)
                {{ $header }}
            @else
                <h2 class="text-base font-semibold text-neutral-900">{{ $title }}</h2>
            @endisset
        </div>
    @endif

    {{ $slot }}
</div>
