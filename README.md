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

## Umami Event Tracking
- Das Umami-Script wird nur in `production` eingebunden (`resources/views/components/layouts/app.blade.php`).
- Statische Klick-Events nutzen `data-umami-event` direkt in Blade.
- Dynamische Events nutzen `window.trackUmamiEvent(name, data)` aus `resources/js/app.js`.
- Livewire sendet dafür Browser-Events über `$this->dispatch('umami-track', ...)`.

### Verwendete Event-Namen
- `header_logo_clicked`
- `admin_link_clicked`
- `home_event_banner_clicked`
- `home_menu_clicked` (`target`: `refueling`, `oil_log`, `expenses`, `chat`)
- `sign_out_clicked`
- `home_login_link_clicked`
- `password_reset_link_clicked`
- `login_start`
- `login_attempt`
- `login_success`
- `login_error` (`error_type`: `validation`, `credentials`)
- `refueling_start`
- `refueling_gas_station_selected`
- `refueling_aircraft_selected`
- `refueling_submit_attempt`
- `refueling_submit_success`
- `refueling_submit_error` (`error_type`: `validation`, `save_failure`)
- `refueling_back_clicked`
- `refueling_success_home_clicked`
- `oil_log_start`
- `oil_log_aircraft_selected`
- `oil_log_submit_attempt`
- `oil_log_submit_success`
- `oil_log_submit_error` (`error_type`: `validation`, `save_failure`)
- `oil_log_back_clicked`
- `expense_start`
- `expense_submit_attempt`
- `expense_submit_success`
- `expense_submit_error` (`error_type`: `validation`, `save_failure`)
- `expense_success_back_clicked`
- `department_start`
- `department_selected`
- `department_submit_attempt`
- `department_submit_success`
- `department_submit_error` (`error_type`: `validation`, `save_failure`)
- `department_descriptions_clicked`
- `event_page_viewed`
- `event_slot_selected`
- `event_enrollment_attempt`
- `event_enrollment_success`
- `event_enrollment_delete_clicked`
- `event_enrollment_delete_attempt`
- `event_enrollment_delete_success`
- `chat_app_store_clicked`
- `chat_google_play_clicked`
- `chat_password_reset_requested`
- `chat_back_clicked`

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
