<!doctype html>
<html>
<head>
    <title>LfV Greven</title>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

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
        {{ $slot }}
    </div>

    @livewire('notifications')
    @filamentScripts
</body>
</html>
