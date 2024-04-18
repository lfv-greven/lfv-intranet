<div>
    <h1>Tanken erfassen</h1>

    <form class="my-10" wire:submit.prevent="save">
        {{ $this->form }}

        <div class="mt-6">
            <x-filament::button type="submit">
                Betankung speichern
            </x-filament::button>
        </div>
    </form>

    <div class="mt-12 text-center">
        <a href="{{ route('home') }}" class="link" wire:navigate>
            zurück
        </a>
    </div>
</div>
