<div>

    <div class="mb-12 mt-6">
        <h1>Intranet</h1>

        @auth
            <p class="text-center">{{ auth()->user()->firstname, }}, was möchtest du tun?</p>
        @else
            <p class="text-center">Was möchtest du tun?</p>
        @endauth
    </div>

    <div class="grid md:grid-cols-2 gap-2">
        <x-home.menu-link :href="route('refueling')" :icon_url="Vite::asset('resources/images/icons/gas-pump.png')" :external="$isIframe">
            Tanken erfassen
        </x-home.menu-link>

        <x-home.menu-link :href="route('oil')" :icon_url="Vite::asset('resources/images/icons/barrel.png')" :external="$isIframe">
            Ölstand erfassen
        </x-home.menu-link>

        @admin
            <x-home.menu-link :href="route('expenses')" :icon_url="Vite::asset('resources/images/icons/expense.png')" :external="$isIframe">
                Auslagenerstattung
            </x-home.menu-link>

            <x-home.menu-link :href="route('department')" :icon_url="Vite::asset('resources/images/icons/teamwork.png')" :external="$isIframe">
                Mein Engagement
            </x-home.menu-link>
        @endadmin

        <x-home.menu-link :href="route('chat')" :icon_url="Vite::asset('resources/images/icons/chat.png')" :external="$isIframe">
            LfV-Chat
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
