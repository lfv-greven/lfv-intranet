<div>
    <h1>Tankvorgang erfassen</h1>

    <x-ui.alert class="my-6">
        <strong>Probleme bei der Eingabe?</strong>
        <u>Zählerstand</u> oder Foto und <u>Kennzeichen</u> an info@sportflugzentrum.de senden und die Daten werden nachgetragen!
    </x-ui.alert>

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
