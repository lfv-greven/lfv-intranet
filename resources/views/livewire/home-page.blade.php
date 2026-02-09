<div class="mx-auto flex max-w-6xl flex-col gap-10 px-6 py-12 lg:py-16">
    <div class="flex flex-col gap-4 text-center lg:text-left">
        <div class="inline-flex items-center gap-3 rounded-full border border-white/70 bg-white/80 px-4 py-2 text-xs font-semibold uppercase tracking-[0.25em] text-neutral-500">
            LfV Intranet
        </div>
        <h1 class="text-left text-4xl font-semibold leading-tight tracking-tight text-neutral-900 lg:text-5xl">
            @auth
                Hi {{ auth()->user()->firstname }}, was möchtest du tun?
            @else
                Was möchtest du tun?
            @endauth
        </h1>
        <p class="max-w-2xl text-lg text-neutral-600">
            Schnellzugriff auf die wichtigsten Aufgaben. Alles, was du regelmäßig erledigst, liegt hier in Reichweite.
        </p>
    </div>

    @if($event)
        <a href="{{ route('event', ['event' => $event->id]) }}"
           class="group relative overflow-hidden rounded-2xl border border-emerald-200/60 bg-gradient-to-r from-emerald-50 via-white to-emerald-50 p-6 shadow-sm transition hover:-translate-y-0.5 hover:shadow-xl">
            <div class="absolute right-0 top-0 h-24 w-24 rounded-full bg-emerald-200/40 blur-2xl"></div>
            <span class="text-sm font-semibold uppercase tracking-[0.2em] text-emerald-600">Anmeldung</span>
            <div class="mt-2 text-xl font-semibold text-emerald-900">{{ $event->title }}</div>
            <div class="mt-2 text-sm text-emerald-700">Jetzt Platz sichern</div>
        </a>
    @endif

    <div class="grid gap-4 md:grid-cols-2">
        <x-home.menu-link :href="route('refueling')" :icon_url="Vite::asset('resources/images/icons/gas-pump.png')">
            Tanken erfassen
        </x-home.menu-link>

        <x-home.menu-link :href="route('oil')" :icon_url="Vite::asset('resources/images/icons/barrel.png')">
            Ölstand erfassen
        </x-home.menu-link>

        <x-home.menu-link :href="route('expenses')" :icon_url="Vite::asset('resources/images/icons/expense.png')">
            Auslagenerstattung
        </x-home.menu-link>

        <x-home.menu-link :href="route('chat')" :icon_url="Vite::asset('resources/images/icons/chat.png')">
            LfV-Chat
        </x-home.menu-link>
    </div>

    @auth
        <div class="pt-6">
            <x-filament::link wire:click="signOut()" wire:confirm="Möchtest du dich wirklich abmelden?" tag="button">
                abmelden
            </x-filament::link>
        </div>
    @else
        <div class="pt-6">
            <a href="{{ route('login') }}" wire:navigate class="link">
                anmelden
            </a>
        </div>
    @endauth
</div>
