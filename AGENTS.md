# Repository Guidelines

## Project Structure & Module Organization
- `app/`: Laravel application code (controllers, models, policies, console, etc.).
- `routes/`: HTTP route definitions (e.g., `routes/web.php`, `routes/api.php`).
- `resources/`: Frontend assets and views (`resources/views`, `resources/css`, `resources/js`, `resources/images`).
- `database/`: Migrations, factories, and seeders.
- `tests/`: PHPUnit tests (`tests/Feature`, `tests/Unit`).
- `public/`: Web entry point and built assets.
- `config/`: Application configuration.

## Build, Test, and Development Commands
- `composer install`: Install PHP dependencies.
- `pnpm install`: Install frontend dependencies (lockfile is `pnpm-lock.yaml`).
- `php artisan serve`: Run the local Laravel server.
- `pnpm dev`: Start Vite in dev mode for frontend assets.
- `pnpm build`: Build production assets via Vite.
- `php artisan migrate`: Run database migrations.
- `php artisan test`: Run the test suite.

## Coding Style & Naming Conventions
- PHP follows PSR-12 with 4-space indentation; class names are StudlyCase, methods/vars are camelCase.
- File names should match class names and namespaces (PSR-4 in `composer.json`).
- Blade view files use `.blade.php` under `resources/views`.
- Format PHP with Laravel Pint: `./vendor/bin/pint`.
- Prefer Laravel syntax and Laravel helpers wherever it is reasonable (e.g. `Str`, `Arr`, collections, `blank()` / `filled()`, `data_get()`), instead of native PHP alternatives.
- For framework-specific patterns (Laravel, Livewire, Filament, Tailwind), default to official current-version documentation and recommended APIs.
- Avoid custom/manual implementations when an official standard solution exists. If you intentionally deviate, document the reason and tradeoff in the PR/change summary.

## Testing Guidelines
- Tests live in `tests/Feature` and `tests/Unit` and use PHPUnit (`phpunit.xml`).
- Name tests `*Test.php` and keep one class per file.
- Prefer `php artisan test` for consistent environment bootstrapping.

## Commit & Pull Request Guidelines
- Commit messages follow a Conventional Commits style seen in history:
  - `feat: add ...`, `fix(scope): ...`, `chore: ...`.
  - Example: `feat(refueling): add tabs`.
- Pull requests should include a clear summary, linked issue (if any), and screenshots for UI changes.
- Call out migrations, config changes, or new env vars in the PR description.

## Security & Configuration Tips
- Copy `.env.example` to `.env` and set `APP_KEY` before running locally.
- Never commit secrets; use environment variables for credentials and API keys.

## Analytics Event Guidelines (Umami)

- Prefer `data-umami-event` for simple, static click tracking on links/buttons.
- Use `window.trackUmamiEvent(name, data)` (programmatic tracking) for dynamic interactions:
  - form lifecycle (`start`, `attempt`, `success`, `error`)
  - selected values (filters, dropdown choices)
  - interactions with existing Alpine logic (menus, sliders, lightboxes, map consent)
- Keep event names in `snake_case`.
- Keep property keys in `snake_case` and values categorical where possible.
- Reuse existing event names and properties before introducing new ones.
- Do not send personal data or free text in tracking payloads:
  - no name, email, phone, message content, or other PII
- If a new event is added or changed, update `README.md` (`Umami Event Tracking` section) in the same change.
