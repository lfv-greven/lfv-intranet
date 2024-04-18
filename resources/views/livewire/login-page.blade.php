<div>
    <h1>Anmelden</h1>
    <p class="text-center">
        Willkommen im LfV Intranet.<br>Bitte melde dich mit deinen <u>Vereinsflieger</u>-Zugangsdaten an.
    </p>

    <form class="max-w-lg mx-auto bg-neutral-50 rounded p-4 mt-10" wire:submit.prevent="login">
        @if($error)
            <div class="rounded-md bg-red-200 text-red-900 p-4 mb-4">
                <strong>Fehler:</strong>
                Die Anmeldung konnte nicht durchgeführt werden. Prüfe deine Zugangsdaten.
            </div>
        @endif

        {{ $this->form }}

        <div class="mt-4">
            {{ $this->submitAction }}
        </div>
    </form>

    <div class="mt-10 text-center">
        <a href="{{ route('home') }}" class="link" wire:navigate>
            Ohne Login fortfahren
        </a>
        |
        <a href="https://vereinsflieger.de/PasswortAnfordern" target="_blank" class="link">
            Passwort anfordern
        </a>
    </div>
</div>
