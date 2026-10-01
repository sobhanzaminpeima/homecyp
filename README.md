# HomeCyp

[HomeCyp](https://homecyp.com) is a multilingual AI real-estate platform for North Cyprus. The root page is a full-screen conversational property advisor; the application also provides searchable listings, projects, resale properties, rentals, editorial content, lead capture, and a Filament administration panel.

## Product capabilities

- Multilingual chat and UI in English, Turkish, Persian, Arabic, Russian, and German.
- Automatic language detection with RTL support for Persian and Arabic.
- Natural-language property search by budget, range, area, bedrooms, category, and intent.
- Verified database-backed property cards; the assistant cannot invent listing IDs.
- Purchase, resale, investment, daily rental, long-term rental, and Airbnb discovery.
- Conversation memory, saved history, lead sign-in, and password recovery.
- Deterministic ROI, mortgage, comparison, timeline, area, and residency tools.
- Knowledge-base RAG with cached embeddings and hybrid search.
- NVIDIA NIM, OpenAI, and Anthropic support with failover and circuit breaking.
- Image/PDF/text attachments, optional OCR/Document AI, and voice input/output.
- Viewing requests, email workflows, WhatsApp handoff, and optional CRM webhooks.
- Admin-managed inventory, imports, branding, chat, LLM settings, analytics, and SEO.

See [AI chat documentation](docs/AI_CHAT.md) for the full request flow and guardrails.

## Technology

| Layer | Technology |
| --- | --- |
| Application | PHP 8.2+, Laravel 12 |
| Interactive UI | Livewire 3, Alpine.js, Tailwind CSS 4 |
| Admin | Filament 3 |
| Database | MySQL in production, SQLite for tests |
| Media | Spatie Media Library, GD/Imagick |
| Frontend build | Vite 7, Node.js 22 recommended |
| AI providers | NVIDIA NIM, OpenAI, Anthropic |
| CI | GitHub Actions, PHPUnit, Vite build |

## Quick start

Requirements: PHP 8.2+, Composer 2, Node.js 20+, npm, and MySQL or SQLite.

```bash
git clone https://github.com/sobhanzaminpeima/homecyp.git
cd homecyp
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
npm install
npm run build
php artisan storage:link
php artisan serve
```

On Windows, create the SQLite file with PowerShell if `touch` is unavailable:

```powershell
New-Item database/database.sqlite -ItemType File -Force
```

Open `http://127.0.0.1:8000`. The admin panel is at `/admin`.

Set initial admin values in `.env` before seeding:

```dotenv
ADMIN_NAME="HomeCyp Admin"
ADMIN_EMAIL=admin@example.test
ADMIN_PASSWORD=choose-a-strong-password
```

No production password or API key is committed to this repository.

## AI configuration

Configure at least one provider. The default is NVIDIA NIM:

```dotenv
LLM_CHAT_PROVIDER=nvidia_nim
LLM_EMBEDDING_PROVIDER=nvidia_nim
LLM_CHAT_FALLBACKS=openai,anthropic
LLM_EMBEDDING_FALLBACKS=openai

NVIDIA_NIM_API_KEY=
OPENAI_API_KEY=
ANTHROPIC_API_KEY=
```

The active provider can also be changed in **Admin → AI → LLM Settings**. Optional integrations:

```dotenv
WHATSAPP_NUMBER=905338456497
CRM_WEBHOOK_URL=
CRM_WEBHOOK_TOKEN=
DOCUMENT_AI_URL=
DOCUMENT_AI_TOKEN=
```

See [.env.production.example](.env.production.example) for the full template.

## Useful commands

```bash
php artisan migrate --force
php artisan db:seed --force
php artisan storage:link
php artisan optimize:clear
php artisan view:cache
php artisan media-library:regenerate --only-missing
php artisan media:recover-property-images
php vendor/phpunit/phpunit/phpunit
npm run build
```

`media:recover-property-images` repairs missing media records from images already on the public disk. It is a recovery tool, not a routine importer.

## Tests and CI

```bash
php artisan migrate --force
php vendor/phpunit/phpunit/phpunit
npm ci
npm run build
```

GitHub Actions runs migrations, PHPUnit, and a production frontend build on every push and pull request. Some shared hosts disable `proc_open`; run PHPUnit directly there instead of `php artisan test`.

## Documentation

- [Documentation index](docs/README.md)

- [Deployment](DEPLOYMENT.md)
- [AI chat and integrations](docs/AI_CHAT.md)
- [Architecture and data flow](docs/ARCHITECTURE.md)
- [Operations and troubleshooting](docs/OPERATIONS.md)
- [Security](SECURITY.md)
- [Contributing](CONTRIBUTING.md)
- [Changelog](CHANGELOG.md)

## Production endpoints

- Application: <https://homecyp.com>
- Listings: <https://homecyp.com/listings>
- Health: <https://homecyp.com/healthz>
- Sitemap: <https://homecyp.com/sitemap.xml>
- Admin: `/admin`

## License

This repository is proprietary to HomeCyp unless the owner provides a separate license. Third-party dependencies retain their respective licenses.
