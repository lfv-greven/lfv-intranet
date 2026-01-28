<?php

use Livewire\Component;

new class extends Component
{
    //
};
?>

<div>
    <div class="my-12 space-y-4 flex flex-col justify-center">
        <img src="{{ Vite::asset('resources/images/owl/check.png') }}" class="h-72 mx-auto" />

        <x-ui.alert type="success">
            <span class="text-2xl">Tankvorgang gespeichert!</span>
        </x-ui.alert>
    </div>

    <div class="mt-12 text-center">
        <a href="{{ route('home') }}" class="link" wire:navigate>
            zurück
        </a>
    </div>
</div>
