<div class="mx-auto flex max-w-3xl flex-col gap-8 px-6 py-12 lg:py-16">
    <div class="space-y-3">
        <h1 class="text-left text-3xl font-semibold text-neutral-900 lg:text-4xl">
            LfV-Chat
        </h1>
        <p class="text-neutral-600">
            Der LfV Greven betreibt einen eigenen Chat-Server mit Standort in Deutschland.
            Hier findest du die schnellen Schritte zur Einrichtung.
        </p>
    </div>

    <div class="grid gap-4 md:grid-cols-2">
        <a href="https://apps.apple.com/de/app/mattermost/id1257222717?itsct=apps_box_link&itscg=30200" target="_blank"
           data-umami-event="chat_app_store_clicked"
           class="group flex items-center justify-between rounded-2xl border border-white/80 bg-white/80 p-5 shadow-[0_18px_50px_rgba(15,23,42,0.08)] transition hover:-translate-y-0.5 hover:shadow-[0_28px_70px_rgba(15,23,42,0.18)]">
            <div class="flex items-center gap-4">
                <div class="grid h-12 w-12 place-items-center rounded-2xl bg-[#f3ede2] ring-1 ring-white/70">
                    <img class="h-6 w-6 opacity-80" src="{{ Vite::asset('resources/images/icons/ios.svg') }}" alt="iOS">
                </div>
                <div>
                    <div class="text-sm font-semibold text-neutral-900">App Store</div>
                    <div class="text-xs text-neutral-500">Mattermost für iOS</div>
                </div>
            </div>
            <x-filament::icon icon="heroicon-s-arrow-up-right" class="h-4 w-4 text-neutral-400 transition group-hover:text-neutral-600" />
        </a>

        <a href="https://play.google.com/store/apps/details?id=com.mattermost.rn" target="_blank"
           data-umami-event="chat_google_play_clicked"
           class="group flex items-center justify-between rounded-2xl border border-white/80 bg-white/80 p-5 shadow-[0_18px_50px_rgba(15,23,42,0.08)] transition hover:-translate-y-0.5 hover:shadow-[0_28px_70px_rgba(15,23,42,0.18)]">
            <div class="flex items-center gap-4">
                <div class="grid h-12 w-12 place-items-center rounded-2xl bg-[#f3ede2] ring-1 ring-white/70">
                    <img class="h-6 w-6 opacity-80" src="{{ Vite::asset('resources/images/icons/android.svg') }}" alt="Android">
                </div>
                <div>
                    <div class="text-sm font-semibold text-neutral-900">Google Play</div>
                    <div class="text-xs text-neutral-500">Mattermost für Android</div>
                </div>
            </div>
            <x-filament::icon icon="heroicon-s-arrow-up-right" class="h-4 w-4 text-neutral-400 transition group-hover:text-neutral-600" />
        </a>
    </div>

    <div class="rounded-2xl border border-white/80 bg-white/80 p-6 shadow-[0_24px_80px_rgba(15,23,42,0.15)] backdrop-blur lg:p-8">
        <div class="text-sm font-semibold uppercase tracking-[0.2em] text-neutral-500">2. Chat-Server verbinden</div>
        <div class="mt-4 grid gap-3 text-sm text-neutral-700">
            <div class="flex items-center justify-between rounded-xl bg-white/80 px-4 py-3">
                <span class="text-neutral-500">Server-URL</span>
                <span class="font-semibold text-neutral-900">chat.sportflugzentrum.de</span>
            </div>
            <div class="flex items-center justify-between rounded-xl bg-white/80 px-4 py-3">
                <span class="text-neutral-500">Anzeigename</span>
                <span class="font-semibold text-neutral-900">LfV-Greven</span>
            </div>
        </div>
    </div>

    <div class="rounded-2xl border border-white/80 bg-white/80 p-6 shadow-[0_24px_80px_rgba(15,23,42,0.15)] backdrop-blur lg:p-8">
        <div class="text-sm font-semibold uppercase tracking-[0.2em] text-neutral-500">3. Deine Login-Daten</div>
        <div class="mt-4 grid gap-3 text-sm text-neutral-700">
            <div class="flex items-center justify-between rounded-xl bg-white/80 px-4 py-3">
                <span class="text-neutral-500">E-Mail-Adresse</span>
                <span class="font-semibold text-neutral-900">{{ auth()->user()->email }}</span>
            </div>
            <div class="flex items-center justify-between rounded-xl bg-white/80 px-4 py-3">
                <span class="text-neutral-500">Passwort</span>
                <span class="font-semibold text-neutral-900">
                    ••••••
                    <button wire:click="$dispatch('openModal', {component: 'chat.reset-passwort-modal'})" class="ml-3 text-xs font-semibold uppercase tracking-[0.2em] text-primary hover:text-[#e24f12]" data-umami-event="chat_password_reset_modal_opened">
                        zurücksetzen
                    </button>
                </span>
            </div>
        </div>
    </div>

    <div class="text-left">
        <a href="{{ route('home') }}" class="link" wire:navigate data-umami-event="chat_back_clicked">
            zurück
        </a>
    </div>
</div>
