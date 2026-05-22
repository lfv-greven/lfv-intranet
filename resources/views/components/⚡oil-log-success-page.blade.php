<?php

use Livewire\Component;

new class extends Component
{
    //
};
?>

<div class="mx-auto flex max-w-3xl flex-col gap-8 px-6 py-12 lg:py-16">
    <div class="rounded-2xl border border-white/80 bg-white/80 p-8 text-center shadow-[0_24px_80px_rgba(15,23,42,0.15)] backdrop-blur">
        <div class="flex justify-center">
            <img src="{{ Vite::asset('resources/images/owl/check.webp') }}" class="h-48 w-48" alt="Saved"/>
        </div>

        <p class="mt-6 text-lg font-semibold text-neutral-900">
            Ölstand gespeichert!
        </p>

        <p class="mt-2 text-sm text-neutral-500">
            Danke für die Eingabe. Du kannst jetzt einen weiteren Ölstand erfassen oder zur Startseite zurückkehren.
        </p>

        <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:justify-center">
            @auth
                <x-filament::button tag="a" :href="route('home')" class="w-full sm:w-auto" data-umami-event="oil_log_success_home_clicked">
                    Zur Startseite
                </x-filament::button>
            @else
                <x-filament::button tag="a" :href="route('oil')" class="w-full sm:w-auto">
                    Weiteren Ölstand erfassen
                </x-filament::button>
            @endauth
        </div>
    </div>
</div>
