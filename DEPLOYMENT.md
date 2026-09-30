# HomeCyp — cPanel Deployment Guide (No Terminal Required)

This guide deploys HomeCyp to standard cPanel shared hosting **without SSH/terminal access**.
Because cPanel can't run `composer`, `npm`, or `php artisan`, everything is prepared locally and
uploaded, then finished through the browser-based installer at `/install`.

---

## 0. Prerequisites on the host

In cPanel → **Select PHP Version** (or MultiPHP), choose **PHP 8.2+** and enable these extensions:

`pdo_mysql`, `mbstring`, `openssl`, `intl`, `gd` (or `imagick`), `fileinfo`, `bcmath`, `ctype`, `curl`, `tokenizer`

> `intl` and `gd` are the two most commonly disabled — make sure both are ticked.

---

## 1. Prepare the package locally (one-time)

Run these on your own machine (where PHP/Composer/Node exist):

```bash
# Install PHP deps for production (no dev tools)
composer install --optimize-autoloader --no-dev

# Build front-end assets
npm install
npm run build

# Generate an app key (copy the output)
php artisan key:generate --show
```

You should now have a `vendor/` folder and a `public/build/` folder. These get uploaded too.

---

## 2. Create the database in cPanel

cPanel → **MySQL Databases**:
1. Create a database (e.g. `youracct_homecyp`).
2. Create a user and a password.
3. **Add the user to the database** with *All Privileges*.

Note the database name, user, and password for the next step.

---

## 3. Configure `.env`

1. Copy `.env.production.example` to `.env`.
2. Fill in `APP_URL`, the `APP_KEY` you generated, and the `DB_*` credentials.
3. Keep `SESSION_DRIVER=file` and `CACHE_STORE=file` (so the installer runs before tables exist).
4. Fill in `MAIL_*` from cPanel → Email Accounts (for password resets, lead notifications, and
   viewing-request confirmation emails sent by the AI chat).
5. Fill in `NVIDIA_NIM_API_KEY` (or switch to OpenAI/Anthropic later from **Admin → AI → LLM Settings**
   without touching `.env` again). Without a key, the AI chat still works and degrades gracefully to a
   "please try again / leave your details" fallback instead of erroring.

---

## 4. Upload the files

Zip the **entire project** locally (including `vendor/` and `public/build/`, excluding `node_modules/`).
In cPanel → **File Manager**, upload and **Extract** it.

Choose ONE of these layouts:

### Option A — Document root = project root (easiest)
- Upload everything into `public_html/`.
- The included root `.htaccess` transparently forwards requests into `public/`.
- Nothing else to configure.

### Option B — Document root = `public/` (cleaner / recommended if available)
- Upload the project **above** `public_html` (e.g. into `/home/youracct/homecyp/`).
- cPanel → **Domains** → set the document root to `/home/youracct/homecyp/public`.
- Delete the root `.htaccess` (not needed in this layout).

---

## 4b. (Optional) Import the ready-made database

A full database export is included at **`database/homecyp.sql`** (admin user, settings, imported
projects, demo properties, blog posts, menus, categories — everything from the local build).

In cPanel → **phpMyAdmin** → select your database → **Import** → choose `database/homecyp.sql` → Go.
If you import this way you can skip the installer's migrate/seed step.

## 5. Run the web installer

**If the site shows a fatal error immediately** (e.g. `vendor/autoload.php ... Permission denied`), the zip
was built on Windows and some folders extracted with the wrong Unix permissions (missing the execute bit,
so they can't be traversed). Visit **`https://yourdomain.com/fix-permissions.php`** once first — it walks
every project folder and corrects this. Safe to leave in place afterward; it does nothing unless visited.

Then visit **`https://yourdomain.com/install`**.

- It checks server requirements (all must be green).
- Click **Install HomeCyp Now** — this fixes app-folder permissions again as a safety net, runs migrations,
  seeds initial data, links storage, and caches config.
- On success the installer **locks itself** (writes `storage/installed.lock`).

---

## 6. Secure & finish

1. Log in at **`/admin`** with:
   - Email: `admin@homecyp.com`
   - Password: `HomeCyp@2024!`
2. **Change the admin password immediately** (Admin → profile).
3. Go to **Admin → Site Settings** and set: contact info, social links, Google Analytics / Meta Pixel IDs, and Google reCAPTCHA keys.
4. Import properties: **Admin → Import from URL** (paste a Northernland project URL), or add listings manually.
5. Go to **Admin → AI** and: confirm the LLM provider is correct, add Knowledge Base sources (area
   guides, legal/residency info, FAQs) and click **Process** on each, review the seeded Recommendation
   Rules and Areas, and set the welcome message / suggested starter cards.

---

## 7. If images don't appear

The installer tries to create the `public/storage` symlink. Some hosts block symlinks. If images 404:
- cPanel → File Manager: create a symlink, **or**
- Copy `storage/app/public/*` into `public/storage/` manually, **or**
- Ask your host to run `php artisan storage:link`.

---

## Updating later

To change code: edit locally, re-run `npm run build` if assets changed, upload the changed files,
then in cPanel delete the cached files in `bootstrap/cache/` (or re-run `/install` after deleting
`storage/installed.lock`) so config/routes refresh.

## Re-running the installer

Delete `storage/installed.lock`, then visit `/install` again. (Seeders are idempotent — they won't
duplicate the admin user or settings.)

---

## Default credentials reference

| Item | Value |
|------|-------|
| Admin URL | `/admin` |
| Admin email | `admin@homecyp.com` |
| Admin password | `HomeCyp@2024!` *(change after first login)* |
| Installer | `/install` (self-locks after use) |
| Sitemap | `/sitemap.xml` |
