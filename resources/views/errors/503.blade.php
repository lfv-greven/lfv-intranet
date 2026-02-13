<x-layouts.app title="Wartung | LfV Intranet">
    <section class="mx-auto flex w-full max-w-6xl flex-col gap-10 px-6 py-16 lg:py-20">
        <div class="grid items-center gap-12 lg:grid-cols-[1.05fr_0.95fr]">
            <div class="flex flex-col gap-6">
                <p class="text-xs font-semibold uppercase tracking-[0.32em] text-neutral-500">Wartungsmodus</p>
                <h1 class="text-4xl font-semibold leading-tight text-neutral-900 sm:text-5xl">
                    Wir sind gleich wieder da.
                </h1>
                <p class="text-base leading-relaxed text-neutral-700 sm:text-lg">
                    Das LfV-Intranet wird gerade aktualisiert. Bitte versuche es in wenigen Minuten erneut.
                </p>
                <div class="flex flex-wrap items-center gap-3 text-xs font-semibold uppercase tracking-[0.2em] text-neutral-500">
                    <span class="rounded-full border border-neutral-300/80 bg-white/70 px-4 py-2">Status: Wartung läuft</span>
                    <span class="rounded-full border border-neutral-300/80 bg-white/70 px-4 py-2">Code: 503</span>
                </div>
            </div>

            <div class="flex items-center justify-center">
                <div class="relative">
                    <div class="absolute -inset-8 rounded-[2.5rem] bg-[#f65812]/20 blur-3xl"></div>
                    <div class="relative rounded-[2rem] border border-neutral-200/80 bg-white/80 p-6 shadow-[0_30px_80px_rgba(15,23,42,0.18)]">
                        <img
                            src="{{ Vite::asset('resources/images/owl/maintenance.png') }}"
                            alt="Eule im Wartungsmodus"
                            class="h-80 w-80 object-contain sm:h-96 sm:w-96 lg:h-[28rem] lg:w-[28rem]"
                        >
                    </div>
                </div>
            </div>
        </div>
    </section>
</x-layouts.app>
