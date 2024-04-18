<div>
    <h1>Intranet</h1>

    @auth
        <p class="text-center">{{ auth()->user()->firstname, }}, was möchtest du tun?</p>
    @else
        <p class="text-center">Was möchtest du tun?</p>
    @endauth

    <a href="{{ route('refueling') }}" wire:navigate class="flex bg-neutral-200 rounded items-center p-4 space-x-4 mt-12">
        <img src="{{ Vite::asset('resources/images/icons/gas-pump.png') }}" alt="Öl" class="h-16">
        <div class="flex-1 font-bold">Tanken erfassen</div>
        <x-filament::icon icon="heroicon-s-chevron-right" class="w-4 h-4" />
    </a>

    <a href="{{ route('oil') }}" wire:navigate class="flex bg-neutral-200 rounded items-center p-4 space-x-4 mt-4">
        <img src="{{ Vite::asset('resources/images/icons/barrel.png') }}" alt="Öl" class="h-16">
        <div class="flex-1 font-bold">Ölstand erfassen</div>
        <x-filament::icon icon="heroicon-s-chevron-right" class="w-4 h-4" />
    </a>

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
