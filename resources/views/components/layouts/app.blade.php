<!doctype html>
<html>
<head>
    <title>LfV Greven</title>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="manifest" href="/site.webmanifest">

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>

    @filamentStyles
    @vite('resources/css/app.css')
</head>
<body class="antialiased">
    <div class="container">
        <a class="flex justify-center" href="{{ url('/') }}">
            <image class="w-2/3" src="{{ Vite::asset('resources/images/logo/logo.svg') }}" alt="Logo" />
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
</body>
</html>
