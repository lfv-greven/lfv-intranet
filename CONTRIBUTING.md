# Contributing

Thanks for contributing to the LfV Intranet.

## Before you start

- Open an issue first for larger changes or security-relevant topics.
- Keep changes focused. Large mixed PRs are hard to review and hard to maintain.
- Do not commit secrets, credentials, exports, or production data.

## Development setup

```bash
composer install
pnpm install
cp .env.example .env
php artisan key:generate
php artisan migrate
pnpm dev
```

Useful commands:

```bash
php artisan test
./vendor/bin/pint
pnpm build
```

## Coding guidelines

- Follow Laravel conventions and prefer framework helpers over custom utility code.
- Keep PHP code PSR-12 compatible.
- Write small, readable changes with clear intent.
- Add or update tests when behavior changes.
- If you add or change Umami events, update the `README.md` section `Umami Event Tracking` in the same change.

## Pull requests

- Use a clear title and explain what changed and why.
- Mention migrations, config changes, or new environment variables explicitly.
- Add screenshots for visible UI changes.
- Call out tradeoffs if you intentionally deviate from a framework default or standard approach.

## Commits

- Use Conventional Commits where possible.
- Preferred examples: `feat: add expense export`, `fix(auth): regenerate session after login`, `chore: update dependencies`
- Keep commit messages short, specific, and scoped to the actual change.

## Licensing

By submitting a contribution, you confirm that:

- you have the right to submit the code or content,
- your contribution does not knowingly violate third-party rights,
- your contribution is provided under the repository license `AGPL-3.0-or-later`.

## Security

Do not open public issues for secrets or exploitable vulnerabilities.

Report security problems directly to the maintainers instead.
