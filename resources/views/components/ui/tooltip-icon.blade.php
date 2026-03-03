@props([
    'label',
    'icon' => 'ℹ️',
])

<span class="group relative inline-flex cursor-default">
    <span
        class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-neutral-100 text-sm"
        aria-label="{{ $label }}"
    >
        {{ $icon }}
    </span>
    <span
        role="tooltip"
        class="pointer-events-none absolute bottom-full left-1/2 z-20 mb-2 hidden -translate-x-1/2 whitespace-nowrap rounded-md bg-neutral-900 px-2 py-1 text-xs text-white shadow-md group-hover:block"
    >
        {{ $label }}
    </span>
</span>
