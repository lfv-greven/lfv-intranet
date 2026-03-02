<x-layouts.app title="Server-Fehler | LfV Intranet">
    <section class="mx-auto flex w-full max-w-5xl flex-col gap-10 px-6 py-16 lg:py-20">
        <div class="grid items-center gap-12 lg:grid-cols-[1.05fr_0.95fr]">
            <div class="flex flex-col gap-6">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.32em] text-neutral-500">Fehler 500</p>
                    <h1 class="text-4xl font-semibold leading-tight text-neutral-900 sm:text-5xl">
                        Server-Fehler
                    </h1>
                </div>
                <p class="text-base leading-relaxed text-neutral-700 sm:text-lg">
                    Es ist ein interner Fehler aufgetreten. Der Fehler wurde bereits erfasst und wir kümmern uns darum.
                </p>
                <p class="text-base leading-relaxed text-neutral-700 sm:text-lg">
                    Wenn dir nun Eintragungen fehlen oder du Fragen hast, melde dich bitte per Mail an
                    <a href="mailto:it@sportflugzentrum.de" class="font-semibold text-[#0b4f6c] hover:underline">it@sportflugzentrum.de</a>.
                </p>
                <p class="text-base leading-relaxed text-neutral-700 sm:text-lg">
                    Bitte versuche es später noch einmal.
                </p>

                <div>
                    <x-filament::button tag="a" :href="route('home')">
                        Zur Startseite
                    </x-filament::button>
                </div>
            </div>

            <div class="flex items-center justify-center">
                <div class="relative rounded-[2rem] border border-neutral-200/80 bg-white/80 p-6 shadow-[0_30px_80px_rgba(15,23,42,0.18)]">
                    <img
                        src="{{ Vite::asset('resources/images/owl/eule-500.png') }}"
                        alt="Eule 500"
                        class="h-80 w-80 object-contain sm:h-96 sm:w-96"
                    >
                </div>
            </div>
        </div>
    </section>
</x-layouts.app>
