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
        </div>

        <div class="rounded-2xl border border-white/80 bg-white/80 p-6 shadow-[0_24px_80px_rgba(15,23,42,0.15)] backdrop-blur lg:p-8">
            <h2 class="text-xl font-semibold text-neutral-900">Anmelden</h2>
            <p class="mt-2 text-sm text-neutral-500">
                Bitte melde dich mit deinen Vereinsflieger‑Zugangsdaten an.
            </p>

            @if($loginMessageTitle !== '' || $loginMessageBody !== '')
                <div class="mt-6 rounded-xl border border-amber-200 bg-amber-50/95 p-4 text-sm text-amber-900 shadow-sm">
                    <div class="flex gap-3">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-amber-100 text-amber-700">
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.88c.673 1.166-.17 2.625-1.516 2.625H3.72c-1.347 0-2.19-1.459-1.516-2.625l6.28-10.88ZM10 6a.75.75 0 0 1 .75.75v3.5a.75.75 0 0 1-1.5 0v-3.5A.75.75 0 0 1 10 6Zm0 7a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd" />
                            </svg>
                        </div>

                        <div class="min-w-0">
                            @if($loginMessageTitle !== '')
                                <p class="font-semibold">{{ $loginMessageTitle }}</p>
                            @endif

                            @if($loginMessageBody !== '')
                                <p class="{{ $loginMessageTitle !== '' ? 'mt-1' : '' }} leading-6 text-amber-800">
                                    {{ $loginMessageBody }}
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            <form
                class="mt-6 grid gap-4"
                wire:submit.prevent="login"
                x-data
                x-on:focusin.once="window.trackUmamiEvent('login_start')"
            >
                @if($errorType !== null)
                    <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                        <strong>Fehler:</strong>
                        @if($errorType === 'credentials')
                            Die Anmeldung konnte nicht durchgeführt werden. Prüfe deine Zugangsdaten.
                        @else
                            Die Anmeldung ist vorübergehend nicht verfügbar. Bitte versuche es in wenigen Minuten erneut.
                        @endif
                    </div>
                @endif

                {{ $this->form }}

                <div class="mt-2">
                    {{ $this->submitAction }}
                </div>
            </form>

            <div class="mt-6 text-center text-sm text-neutral-500">
                <a href="https://vereinsflieger.de/PasswortAnfordern" target="_blank" class="link" data-umami-event="password_reset_link_clicked">
                    Passwort anfordern
                </a>
            </div>
        </div>
    </div>
</div>
