<div class="mx-auto flex max-w-3xl flex-col gap-8 px-6 py-12 lg:py-16">
    <div class="space-y-3">
        <h1 class="text-left text-3xl font-semibold text-neutral-900 lg:text-4xl">
            Tankvorgang erfassen
        </h1>
        <p class="text-neutral-600">
            Erfasse den Tankvorgang direkt vor Ort. Die Angaben werden automatisch ins System übernommen.
        </p>
    </div>

        <form class="grid gap-6" wire:submit.prevent="save">
            {{ $this->form }}

            <div class="pt-2">
                <x-filament::button type="submit" class="w-full">
                    Betankung speichern
                </x-filament::button>
            </div>
        </form>

    <div class="text-left">
        <a href="{{ route('home') }}" class="link" wire:navigate>
            zurück
        </a>
    </div>
</div>
