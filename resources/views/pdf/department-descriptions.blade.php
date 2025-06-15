<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Teams</title>
    
    <style>
        {!! Vite::content('resources/css/pdf/department-descriptions.css') !!}
    </style>
</head>
<body>
<div>
    <div class="mb-8 flex flex-col items-center">
        <image class="w-full" src="{{ Vite::asset('resources/images/logo/logo.svg') }}" alt="Logo"/>

        <h1 class="font-bold text-2xl">Team-Beschreibungen</h1>
        <p class="text-justify mt-1">
            Um die anfallenden Aufgaben und Herausforderungen, die unsere Vereinsinfrastruktur an uns stellt, besser
            bewältigen zu können, suchen wir zur Unterstützung bereichsspezifische Koordinatoren, welche die
            Kommunikation und Verantwortung für Ihren jeweiligen Bereich übernehmen.
        </p>
    </div>

    <div class="grid grid-cols-1 divide-y divide-slate-300">
        @foreach($departments as $department)
            <div class="py-4 prose mx-auto max-w-full text-sm">
                <h2 class="underline font-bold">{{ $department->name }}</h2>
                {!! $department->description !!}
            </div>
        @endforeach
    </div>
</div>
</body>
</html>
