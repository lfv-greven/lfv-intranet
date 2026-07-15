# LfV Intranet

LfV Intranet is the internal platform of Luftfahrtvereinigung Greven e.V. for member-facing workflows and internal administration.

It covers recurring club processes such as refueling, oil logging, expense submissions, department coordination, chat onboarding, and administrative back-office tasks.

## Features

- Member login via Vereinsflieger
- Mobile-optimized forms for operational workflows
- Filament admin panel for internal processes
- Background jobs and queue-based integrations

## Tech Stack

- Laravel 13
- Livewire 4
- Filament 5
- Tailwind CSS 4
- MySQL

## Local Setup

```bash
composer install
npm ci
cp .env.example .env
php artisan key:generate
php artisan migrate
npm run dev
```

## Important Environment Variables

- `VF_USERNAME`, `VF_PASSWORD`, `VF_APPKEY`, `VF_CID`: Vereinsflieger credentials
- `VF_CACHE_STORE`, `VF_RATE_LIMIT_NAMESPACE`: shared cache store and namespace for the Vereinsflieger request gate
- `VF_TOKEN_TTL_SECONDS`, `VF_CONNECT_TIMEOUT_SECONDS`, `VF_TIMEOUT_SECONDS`, `VF_LOCK_SECONDS`: cached service-token lifetime, HTTP timeouts, and shared-lock lifetime
- `FI_WORKHOURS_CATEGORY_ID`: workhours category id used for FI workflows (default: `8471`)

## Vereinsflieger Integration

All Vereinsflieger access goes through `App\Services\VereinsfliegerClient`. The client uses a shared cache lock to pace requests below the upstream limits, caches the service token, and stops login attempts before the upstream failed-login lock can be reached.

For installations served from multiple domains or application instances, every instance must use the same `VF_CACHE_STORE`, cache prefix, `VF_RATE_LIMIT_NAMESPACE`, and `APP_KEY`. The cache store must support atomic locks; Laravel's database cache store is sufficient when all instances use the same database. The low-priority queue worker in `docker/supervisor/queue.conf` must be deployed alongside the default queue worker.

## Development

- Start the backend: `php artisan serve`
- Start the frontend: `npm run dev`
- Run tests: `php artisan test`
- Run code style fixes: `./vendor/bin/pint`
- Build production assets: `npm run build`

## Umami Event Tracking

- The Umami script is only loaded in `production` from [app.blade.php](/Users/oliver/Entwicklung/lfv-intranet/resources/views/components/layouts/app.blade.php).
- Static click tracking uses `data-umami-event` directly in Blade templates.
- Dynamic tracking uses `window.trackUmamiEvent(name, data)` from [app.js](/Users/oliver/Entwicklung/lfv-intranet/resources/js/app.js).
- Livewire dispatches browser events through `$this->dispatch('umami-track', ...)`.

### Event Names In Use

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
- `login_error` (`error_type`: `validation`, `credentials`, `rate_limited`, `service_unavailable`)
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
- `oil_log_success_home_clicked`
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

## Project Structure

- `app/`: Laravel application code such as models, jobs, and services
- `resources/`: Blade views, CSS, JavaScript, and static assets
- `routes/`: HTTP route definitions
- `database/`: migrations, factories, and seeders
- `tests/`: automated tests

## Notes

- Do not commit secrets.
- Use `VereinsfliegerPriority::HIGH` for interactive, real-time calls such as login and fuel prices. Use `VereinsfliegerPriority::LOW` for exports, FI settlement, and scheduled synchronization.

## Contributing

Contributions are welcome.

Please read [CONTRIBUTING.md](/Users/oliver/Entwicklung/lfv-intranet/CONTRIBUTING.md) before opening your first pull request.

By submitting a contribution, you confirm that you have the necessary rights to your changes and that you provide them under `AGPL-3.0-or-later`.

## Security

Please do not report vulnerabilities in public issues.

See [SECURITY.md](/Users/oliver/Entwicklung/lfv-intranet/SECURITY.md) for the reporting process.

## License

This project is licensed under the `GNU Affero General Public License v3.0 or later (AGPL-3.0-or-later)`.

See [LICENSE](/Users/oliver/Entwicklung/lfv-intranet/LICENSE) for the full license text.
