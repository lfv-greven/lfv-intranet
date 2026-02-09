<div class="mx-auto flex max-w-3xl flex-col gap-8 px-6 py-12 lg:py-16">
    @if($saved)
        <div class="rounded-2xl border border-white/80 bg-white/80 p-8 text-center shadow-[0_24px_80px_rgba(15,23,42,0.15)] backdrop-blur">
            <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-emerald-100">
                <img src="{{ Vite::asset('resources/images/icons/checked.png') }}" class="h-10 w-10"/>
            </div>

            <p class="mt-6 text-lg font-semibold text-neutral-900">
                Deine Daten wurden gespeichert!
            </p>

            <p class="mt-2 text-sm text-neutral-500">
                Wir prüfen deine Angaben und melden uns bei Rückfragen.
            </p>

            <div class="mt-6">
                <x-filament::link :href="url('/')">
                    Zurück
                </x-filament::link>
            </div>
        </div>
    @else
        <div class="space-y-3">
            <h1 class="text-left text-3xl font-semibold text-neutral-900 lg:text-4xl">
                Auslagenerstattung
            </h1>
            <p class="text-neutral-600">
                Bitte lade deinen Beleg hier hoch und achte darauf, dass alle Details gut lesbar sind.
                Falls eine Rechnungsadresse aufgedruckt ist, muss diese die Vereinsadresse sein.
            </p>
        </div>

        <div class="rounded-2xl border border-white/80 bg-white/80 p-6 shadow-[0_24px_80px_rgba(15,23,42,0.15)] backdrop-blur lg:p-8">
            <form class="grid gap-6" wire:submit.prevent="store">
                {{ $this->form }}

                <x-filament::button class="w-full" type="submit">
                    Beleg einreichen
                </x-filament::button>
            </form>
        </div>
    @endif
</div>
