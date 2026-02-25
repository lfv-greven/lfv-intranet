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
        @production
            <script defer src="https://analytics.sportflugzentrum.de/script.js" data-website-id="37271ba7-0db8-4676-8811-54b81bc0368c"></script>
        @endproduction
        @livewireStyles
        @filamentStyles
    </head>
    <body class="antialiased bg-[#f5f2ea] text-neutral-900">
        <div class="relative min-h-dvh overflow-hidden">
            <div class="pointer-events-none absolute -left-40 -top-48 h-[28rem] w-[28rem] rounded-full bg-[#f65812]/20 blur-3xl"></div>
            <div class="pointer-events-none absolute right-[-10rem] top-24 h-[26rem] w-[26rem] rounded-full bg-[#0b4f6c]/18 blur-3xl"></div>
            <div class="pointer-events-none absolute -bottom-32 left-1/2 h-[30rem] w-[30rem] -translate-x-1/2 rounded-full bg-white/70 blur-3xl"></div>

            <header class="relative z-10">
                <div class="mx-auto flex max-w-6xl items-center justify-between px-6 py-6">
                    <a class="flex items-center gap-3" href="{{ url('/') }}" data-umami-event="header_logo_clicked">
                        <span class="grid h-12 w-12 place-items-center rounded-2xl bg-white/80 shadow-sm">
                            <img class="h-8 w-8" src="{{ Vite::asset('resources/images/logo/brandsign.svg') }}" alt="Logo" />
                        </span>
                        <span class="text-sm font-semibold uppercase tracking-[0.2em] text-neutral-500">
                            {{ config('app.name') }}
                        </span>
                    </a>
                    @if(auth()->user()?->isAdmin())
                        <a href="{{ url('/admin') }}" target="_blank" class="text-xs font-semibold uppercase tracking-[0.2em] text-neutral-500 hover:text-neutral-700" data-umami-event="admin_link_clicked">
                            Admin
                        </a>
                    @endif
                </div>
            </header>

            <main class="relative z-10">
                {{ $slot }}
            </main>
        </div>

        @livewire('notifications')
        @filamentScripts
        @livewire('wire-elements-modal')
        @livewireScripts
    </body>
</html>
