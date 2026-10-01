# Contributing

## Workflow

1. Create a focused branch from the latest default branch.
2. Keep credentials and generated artifacts out of commits.
3. Follow existing Laravel, Livewire, Blade, and Filament patterns.
4. Add migrations for schema changes.
5. Run relevant tests and the production frontend build.
6. Describe behavior, deployment, and validation in the pull request.

## Validation

```bash
php vendor/phpunit/phpunit/phpunit
npm ci
npm run build
git diff --check
```

For chat changes, verify English and one RTL conversation, no-match and matching-property queries, and the new-conversation empty state.

## Translation

Interface translations live in `lang/*.json`. Wrap visible Blade strings in `__()`. Admin-managed welcome/card text is translated when its source value matches a dictionary key.

## Database and media

Use factories and seeders for reproducible development data. Do not commit database dumps or uploaded media. Run recovery commands only after a backup.
