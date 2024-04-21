@props([
    'icon' => null,
    'type' => 'info',
    'class' => '',
])

@php
$border = match($type) {
    'info' => 'border-blue-400',
};

$bg = match($type) {
    'info' => 'bg-blue-50',
};

$text = match($type) {
    'info' => 'text-blue-700',
};

$iconColor= match($type) {
    'info' => 'text-blue-400',
};

$defaultIcon= match($type) {
    'info' => 'heroicon-s-information-circle',
};
@endphp

<div class="border-l-4 {{ $border }} {{ $bg }} p-4 {{ $class }}">
    <div class="flex">
        <div class="flex-shrink-0">
            <x-filament::icon :icon="$icon ?? $defaultIcon" class="h-5 w-5 {{ $iconColor }}" />
        </div>
        <div class="ml-3 flex-1">
            <p class="text-sm text-center {{ $text }}">
                {{ $slot }}
            </p>
        </div>
    </div>
</div>
