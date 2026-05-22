<div class="mx-auto flex max-w-3xl flex-col gap-8 px-6 py-12 lg:py-16">
    <div class="space-y-3">
        <h1 class="text-left text-3xl font-semibold text-neutral-900 lg:text-4xl">
            Ölstand erfassen
        </h1>
        <p class="text-neutral-600">
            Trage den aktuellen Ölstand <b>vor jedem Flug</b> ein, damit der Ölverbrauch durch die Technik nachverfolgt werden kann.
        </p>
    </div>

    <form class="grid gap-6" wire:submit.prevent="save" x-data x-on:focusin.once="window.trackUmamiEvent('oil_log_start')">
        {{ $this->form }}

        <div class="pt-2">
            <x-filament::button type="submit" class="w-full">
                Ölstand erfassen
            </x-filament::button>
        </div>
    </form>

    @auth
        <div class="text-left">
            <a href="{{ route('home') }}" class="link" wire:navigate data-umami-event="oil_log_back_clicked">
                zurück
            </a>
        </div>
    @endauth
</div>
