<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>{{ $title ?? config('app.name') }}</title>

        <style>
            [x-cloak] {
                display: none !important;
            }
        </style>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
        @filamentStyles
    </head>
    <body class="antialiased">
        <div class="container">
            <a class="flex justify-center" href="{{ url('/') }}">
                <image class="h-24" src="{{ Vite::asset('resources/images/logo/logo.svg') }}" alt="Logo" />
            </a>

            {{ $slot }}
        </div>

        @if(auth()->user()?->isAdmin())
            <div class="text-center">
                <a href="{{ url('/admin') }}" target="_blank" class="text-neutral-700 text-xs hover:underline">
                    admin
                </a>
            </div>
        @endif

        @livewire('notifications')
        @filamentScripts
        @livewire('wire-elements-modal')
        @livewireScripts
    </body>
</html>
