<div>
    <div class="flex justify-center">
        <image class="w-2/3" src="{{ Vite::asset('resources/images/logo/logo.svg') }}" alt="Logo" />
    </div>

    <form class="my-10" wire:submit.prevent="save">
        {{ $this->form }}

        <div class="mt-6">
            <x-filament::button type="submit">
                Betankung speichern
            </x-filament::button>
        </div>
    </form>

    <div class="mt-12 text-center">
        <x-filament::link :href="route('home')">
            zurück
        </x-filament::link>
    </div>
</div>
