# HomeCyp deployment guide

This guide covers MySQL production deployments on shared hosting or a VPS. The current production site uses a project-root deployment behind the included root `.htaccess`.

## Server requirements

- PHP 8.2 or newer.
- MySQL 8 or a compatible MariaDB release.
- Extensions: `bcmath`, `ctype`, `curl`, `fileinfo`, `gd` or `imagick`, `intl`, `mbstring`, `openssl`, `pdo_mysql`, `tokenizer`, and `xml`.
- Writable `storage/` and `bootstrap/cache/` directories.
- Node.js only on the build machine.

Shared hosting may disable `proc_open`. HomeCyp media conversions use `nonOptimized()` so GD thumbnails still work without external optimization binaries.

## Production environment

Copy `.env.production.example` to `.env` and fill every required value. Never commit `.env`.

Required groups:

1. `APP_KEY`, `APP_URL`, and production flags.
2. MySQL credentials.
3. SMTP credentials for password reset and workflow email.
4. `ADMIN_NAME`, `ADMIN_EMAIL`, and `ADMIN_PASSWORD` before the first seed.
5. At least one configured LLM provider.

Use file sessions/cache and synchronous queues on basic shared hosting. A VPS may use Redis and queue workers.

## Build an artifact

Run on a trusted build machine:

```bash
composer install --no-dev --classmap-authoritative --no-interaction
npm ci
npm run build
php vendor/phpunit/phpunit/phpunit
```

The artifact must contain source, `vendor/`, and `public/build/`. Exclude `.env`, `.git`, `node_modules`, local databases, logs, backups, and test caches.

## Directory layouts

### Project root as document root

Upload the project to `public_html/`. The included root `.htaccess` forwards requests to `public/`.

### Public directory as document root

Upload outside the public directory and point the domain root to the project's `public/` directory. Prefer this when supported.

## First deployment

With SSH:

```bash
php artisan migrate --force
php artisan db:seed --force
php artisan storage:link
php artisan optimize:clear
php artisan view:cache
```

Without SSH, use `/install`. It checks requirements, runs migrations and seeders, creates the storage link, warms caches, and locks itself with `storage/installed.lock`.

After seeding, sign in at `/admin` using the credentials from `.env`, change the password, and remove `ADMIN_PASSWORD` from the environment if your workflow permits it.

## Updating an existing deployment

1. Back up the database and application files.
2. Upload the release without replacing `.env` or `storage/app/public`.
3. Run:

```bash
php artisan migrate --force
php artisan optimize:clear
php artisan view:cache
```

4. Build frontend assets before upload whenever frontend source changes.
5. Verify `/healthz`, chat, a listing image, and `/admin`.

## Media storage and recovery

The public link must resolve as:

```text
public/storage -> storage/app/public
```

If the database lost media records but files remain, back up first and run:

```bash
php artisan media:recover-property-images --gallery=4
php artisan media-library:regenerate --only-missing
```

## Health and rollback

`GET /healthz` checks Laravel, database, and cache. A healthy response is:

```json
{"status":"ok","database":"ok","cache":"ok"}
```

For rollback, restore the previous code artifact and matching database backup, then run `php artisan optimize:clear`.

See [operations](docs/OPERATIONS.md) for verification and troubleshooting.
