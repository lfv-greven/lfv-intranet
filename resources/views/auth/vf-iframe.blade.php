<x-layouts.app>
    <div class="mx-auto flex max-w-3xl flex-col gap-8 px-6 py-12 lg:py-16">
        <div class="space-y-3">
            <h1 class="text-left text-3xl font-semibold text-neutral-900 lg:text-4xl">
                Intranet
            </h1>
            <p class="text-neutral-600">
                Der Login läuft über Vereinsflieger. Bestätige den Zugriff, um das Intranet zu öffnen.
            </p>
        </div>

        @if(filled($errorMessage ?? null))
            <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                {{ $errorMessage }}
            </div>
        @endif

        @if($loginUrl)
            <div class="rounded-2xl border border-white/80 bg-white/80 p-6 shadow-[0_24px_80px_rgba(15,23,42,0.15)] backdrop-blur lg:p-8">
                <a class="flex w-full items-center justify-center rounded-xl bg-[#f65812] px-6 py-4 text-sm font-semibold uppercase tracking-[0.2em] text-white shadow-lg transition hover:-translate-y-0.5 hover:bg-[#e24f12]" href="{{ $loginUrl }}" target="_blank">
                    Intranet öffnen
                </a>
            </div>
        @endif
    </div>
</x-layouts.app>
