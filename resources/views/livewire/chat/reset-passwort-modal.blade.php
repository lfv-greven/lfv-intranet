<div class="p-8">
    <h1 class="mb-4">Passwort für LfV-Chat zurücksetzen</h1>
    <p class="mb-10">
        {{ auth()->user()->firstname }}, aus Sicherheitsgründen können wir das Passwort nicht anzeigen.
        Du kannst aber ein neues Passwort per E-Mail anfordern.
    </p>

    @if($status == 'error')
        <x-ui.alert class="mb-10" type="error">
            <strong>Fehler.</strong> Das Passwort konnte nicht zurückgesetzt werden.
            Probiere es später erneut.
        </x-ui.alert>
    @elseif($status == 'success')
        <x-ui.alert class="mb-10" type="success">
            Prüfe dein E-Mail Postfach. Wir haben dir eine E-Mail geschickt.
        </x-ui.alert>
    @endif

    <x-filament::button wire:click="doResetPassword()">
        Neues Passwort per E-Mail anfordern
    </x-filament::button>
</div>
