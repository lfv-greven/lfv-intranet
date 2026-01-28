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
