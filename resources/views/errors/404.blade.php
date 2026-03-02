<x-layouts.app title="Seite nicht gefunden | LfV Intranet">
    <section class="mx-auto flex w-full max-w-4xl flex-col items-center gap-8 px-6 py-16 text-center lg:py-24">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.32em] text-neutral-500">Fehler 404</p>
            <div class="max-w-2xl space-y-3">
                <h1 class="text-3xl font-semibold text-neutral-900 sm:text-4xl">Seite nicht gefunden</h1>
                <p class="text-base text-neutral-700 sm:text-lg">
                    Die angeforderte Seite existiert nicht oder wurde verschoben.
                </p>
            </div>
        </div>

        <x-filament::button tag="a" :href="route('home')">
            Zur Startseite
        </x-filament::button>

        <div class="rounded-[2rem] border border-neutral-200/80 bg-white/80 p-6 shadow-[0_24px_70px_rgba(15,23,42,0.14)]">
            <img
                src="{{ Vite::asset('resources/images/owl/eule-404.webp') }}"
                alt="Eule 404"
                class="h-72 w-72 object-contain sm:h-80 sm:w-80"
            >
        </div>
    </section>
</x-layouts.app>
