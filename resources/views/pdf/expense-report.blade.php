<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Auslagenerstattung - LfV Greven</title>
    
    <style>
        {!! Vite::content('resources/css/pdf/expense-report.css') !!}
    </style>
</head>
<body>
<div class="container">
    <div class="header">Auslagenerstattung</div>
    <div class="subheader">Luftfahrtvereinigung Greven e.V.</div>

    <div class="content">
        <p><strong>Name des Antragstellers:</strong> {{ $expense->user->name }}</p>
        <p><strong>Datum der Antragstellung:</strong> {{ $expense->created_at->format('d.m.Y') }}</p>
        <p><strong>Beschreibung der Auslagen:</strong> {{ $expense->reason }}</p>
    </div>

    <div class="declaration">
        <p class="mb-2">
            Ich bitte um die Erstattung meiner oben aufgeführten Auslagen auf das nachfolgend angegebene Konto:
        </p>

        <table>
            <tbody>
            <tr>
                <th>IBAN:</th>
                <td>{{ $expense->iban }}</td>
            </tr>
            <tr>
                <th>BIC:</th>
                <td>{{ $expense->bic }}</td>
            </tr>
            </tbody>
        </table>
    </div>

    <div class="meta">
        <div class="content">
            <p>
                <strong>Intranet ID:</strong>
                {{ $expense->id }}
            </p>
            <p>
                <strong>Bericht erstellt am:</strong>
                {{ now()->format('d.m.Y H:i:s') }}
            </p>
        </div>
    </div>

    @if(Str::contains($mime = Storage::mimeType($expense->receipt_filename), 'image'))
        @php
            $base64Image = base64_encode(Storage::read($expense->receipt_filename));
        @endphp

        <div class="break-before-page flex justify-center items-center">
            <img src="data:{{ $mime }};base64,{{ $base64Image }}" class="receipt-image">
        </div>
    @endif
</div>
</body>
</html>
