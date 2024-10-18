<div class="max-w-screen-md mx-auto">
    @if($saved)
        <div class="flex flex-col items-center justify-center">
            <img src="{{ Vite::asset('resources/images/icons/checked.png') }}" class="w-24 h-24"/>

            <p class="my-4 font-bold text-lg">
                Deine Daten wurden gespeichert!
            </p>

            <x-filament::link :href="url('/')">
                Zurück
            </x-filament::link>
        </div>
    @else
        <h1>Auslagenerstattung</h1>
        <p class="text-center my-4 text-sm">
            Bitte lade deinen Beleg hier hoch und achte darauf, dass alle Details gut lesbar sind.
            Falls eine Rechnungsadresse aufgedruckt ist, muss diese die Vereinsadresse sein.
        </p>

        <form wire:submit.prevent="store">
            {{ $this->form }}

            <x-filament::button class="mt-4" type="submit">
                Beleg einreichen
            </x-filament::button>
        </form>
    @endif
</div>
