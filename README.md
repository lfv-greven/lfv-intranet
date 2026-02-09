# LfV Intranet

Das LfV Intranet ist die interne Plattform der Luftfahrtvereinigung Greven e.V. für Mitglieder‑Workflows (z. B. Tanken, Ölstand, Auslagenerstattung, Chat) und Admin‑Prozesse.

## Features
- Mitglieder‑Login über Vereinsflieger
- Mobile‑optimierte Eingabeformulare für operative Abläufe
- Filament Admin für interne Prozesse
- Hintergrundjobs (Queue) für Integrationen

## Tech Stack
- Laravel 12
- Livewire 4
- Filament 5
- Tailwind CSS 4
- MySQL

## Setup (lokal)
```bash
composer install
pnpm install
cp .env.example .env
php artisan key:generate
php artisan migrate
pnpm dev
```

## Wichtige Umgebungsvariablen
- `VF_USERNAME`, `VF_PASSWORD`, `VF_APPKEY`, `VF_CID` – Vereinsflieger Zugang
- `FI_WORKHOURS_CATEGORY_ID` – Kategorie‑ID für Workhours (Standard: `8471`)

## Development
- Start Backend: `php artisan serve`
- Start Frontend: `pnpm dev`
- Tests: `php artisan test`

## Struktur
- `app/` – Laravel Anwendung (Models, Jobs, Services)
- `resources/` – Views, CSS, JS, Assets
- `routes/` – HTTP Routen
- `database/` – Migrations
- `tests/` – Tests

## Hinweise
- Keine Secrets committen.
- Datenzugriff auf Vereinsflieger erfolgt über `App\Services\VereinsfliegerClient` (frischer Login + Retry).

## Lizenz
Proprietär – nur für interne Nutzung der LfV Greven e.V.
