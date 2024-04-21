<div>

    <div class="mb-12 mt-6">
        <h1>Intranet</h1>

        @auth
            <p class="text-center">{{ auth()->user()->firstname, }}, was möchtest du tun?</p>
        @else
            <p class="text-center">Was möchtest du tun?</p>
        @endauth
    </div>

    <x-ui.alert class="mb-6">
        <strong>Update des Tankbuchs:</strong>
        Bei Problemen, Fragen oder Anregungen Mail an info@sportflugzentrum.de. Danke!
    </x-ui.alert>

    <div class="space-y-6">
        <x-home.menu-link :href="route('refueling')" :icon_url="Vite::asset('resources/images/icons/gas-pump.png')" :external="$isIframe">
            Tanken erfassen
        </x-home.menu-link>

        <x-home.menu-link :href="route('oil')" :icon_url="Vite::asset('resources/images/icons/barrel.png')" :external="$isIframe">
            Ölstand erfassen
        </x-home.menu-link>
    </div>

    @auth
        <div class="mt-12 text-center">
            <x-filament::link wire:click="signOut()" wire:confirm="Möchtest du dich wirklich abmelden?" tag="button">
                abmelden
            </x-filament::link>
        </div>
    @else
        <div class="mt-12 text-center">
            <a href="{{ route('login') }}" wire:navigate class="link">
                anmelden
            </a>
        </div>
    @endauth
</div>
