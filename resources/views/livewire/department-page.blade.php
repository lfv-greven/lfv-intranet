<div class="max-w-screen-md mx-auto">
    <h1>Mein Engagement</h1>
    <p class="text-center my-4 text-sm">
        Wähle dein Team aus, für das du dich Engagieren möchtest.
    </p>

    <form wire:submit.prevent="store">
        {{ $this->form }}

        <x-filament::button class="mt-4 w-full" type="submit" :disabled="!$canChange">
            Speichern
        </x-filament::button>
    </form>
</div>
