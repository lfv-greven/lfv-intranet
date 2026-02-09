<div class="mx-auto max-w-6xl px-6 py-14 lg:py-20">
    <div class="grid gap-12 lg:grid-cols-2 lg:items-center">
        <div class="space-y-6">
            <div class="inline-flex items-center gap-3 rounded-full border border-white/60 bg-white/70 px-4 py-2 text-sm font-semibold uppercase tracking-[0.2em] text-neutral-600">
                LfV Intranet
            </div>
            <h1 class="text-left text-4xl font-semibold leading-tight tracking-tight text-neutral-900 lg:text-5xl">
                Willkommen zurück
            </h1>
            <p class="text-lg text-neutral-600">
                Melde dich mit deinen Vereinsflieger‑Zugangsdaten an und verwalte deine Vorgänge an einem Ort.
            </p>
            <div class="grid gap-3 text-sm text-neutral-500">
                <div class="flex items-center gap-3">
                    <span class="h-2 w-2 rounded-full bg-[#f65812]"></span>
                    Sicherer Zugriff nur für Mitglieder
                </div>
                <div class="flex items-center gap-3">
                    <span class="h-2 w-2 rounded-full bg-[#f65812]"></span>
                    Optimiert für mobile Geräte
                </div>
                <div class="flex items-center gap-3">
                    <span class="h-2 w-2 rounded-full bg-[#f65812]"></span>
                    Schnelle Workflows ohne Umwege
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-white/80 bg-white/80 p-6 shadow-[0_24px_80px_rgba(15,23,42,0.15)] backdrop-blur lg:p-8">
            <h2 class="text-xl font-semibold text-neutral-900">Anmelden</h2>
            <p class="mt-2 text-sm text-neutral-500">
                Bitte melde dich mit deinen Vereinsflieger‑Zugangsdaten an.
            </p>

            <form class="mt-6 grid gap-4" wire:submit.prevent="login">
                @if($error)
                    <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                        <strong>Fehler:</strong>
                        Die Anmeldung konnte nicht durchgeführt werden. Prüfe deine Zugangsdaten.
                    </div>
                @endif

                {{ $this->form }}

                <div class="mt-2">
                    {{ $this->submitAction }}
                </div>
            </form>

            <div class="mt-6 text-center text-sm text-neutral-500">
                <a href="https://vereinsflieger.de/PasswortAnfordern" target="_blank" class="link">
                    Passwort anfordern
                </a>
            </div>
        </div>
    </div>
</div>
