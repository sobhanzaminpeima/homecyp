# Operations and troubleshooting

## Release checklist

1. Confirm the intended Git commit and a clean worktree.
2. Run PHPUnit and `npm run build`.
3. Back up the production database and files.
4. Upload while preserving `.env` and `storage/app/public`.
5. Run migrations, clear caches, and rebuild the view cache.
6. Verify `/healthz`, English and Persian chat, one property page/image, and `/admin`.
7. Confirm the deployed commit is on the GitHub default branch.

## Production commands

```bash
php artisan migrate --force
php artisan optimize:clear
php artisan view:cache
php vendor/phpunit/phpunit/phpunit
```

If `proc_open` is disabled, `php artisan test` may fail before tests start. Run PHPUnit directly.

## Health check

```bash
curl -fsS https://homecyp.com/healthz
```

HTTP 200 means Laravel, database, and cache checks passed. HTTP 503 means a database/cache failure; inspect `storage/logs/laravel.log`.

## Images do not load

1. Confirm `public/storage` points to `storage/app/public`.
2. Request a known image and confirm HTTP 200 plus an image content type.
3. Check media rows and files.
4. Generate missing conversions:

```bash
php artisan media-library:regenerate --only-missing
```

5. If records were lost but source images remain, back up and run:

```bash
php artisan media:recover-property-images --gallery=4
```

## Chat returns no AI answer

1. Verify the selected provider API key.
2. Review `storage/logs/laravel.log` for status/timeout details.
3. Confirm outbound HTTPS is allowed.
4. Check admin provider and fallback order.
5. Clear cached circuit breakers only after resolving the provider issue.

## Incorrect language

- Confirm the interface dictionary contains the source string.
- Dynamic welcome/card text is translated at render time.
- A user message can change the conversation locale.
- Persian and Arabic must render with `dir="rtl"`.

## Queue, email, and backups

Shared hosting uses `QUEUE_CONNECTION=sync`. On a VPS, configure a durable queue and supervise `php artisan queue:work`. Verify SMTP before workflow notifications.

Back up MySQL, `.env` in a secure secret store, `storage/app/public`, and the deployed Git commit. Never commit production secrets or database exports.
