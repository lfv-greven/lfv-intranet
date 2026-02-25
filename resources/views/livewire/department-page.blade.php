<div class="max-w-screen-md mx-auto">
    <h1>Mein Engagement</h1>
    <p class="text-center my-4 text-sm">
        Wähle dein Team aus, für das du dich Engagieren möchtest. Eine Änderung ist immer zum Jahreswechsel möglich.
        <a href="{{ route('department.descriptions-team') }}" target="_blank" class="link" data-umami-event="department_descriptions_clicked">Team-Beschreibungen ansehen</a>
    </p>

    @if(auth()->user()->department_id)
        <div class="my-6 flex gap-4 items-center justify-center">
            <img src="{{ Vite::asset('resources/images/icons/checked.png') }}" alt="Checked" class="w-12 h-12">
            <span class="font-bold">Danke, dein Engagement wurde gespeichert!</span>
        </div>
    @endif

    <form wire:submit.prevent="store" x-data x-on:focusin.once="window.trackUmamiEvent('department_start')">
        {{ $this->form }}

        <x-filament::button class="mt-4 w-full" type="submit">
            Speichern
        </x-filament::button>
    </form>
</div>
