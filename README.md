# Easy Cyprus

Easy Cyprus is a multilingual, mobile-first platform for discovering businesses, property listings, local deals and events across Northern Cyprus.

## AI property concierge

The `/ai` experience gives visitors a ChatGPT-style property assistant that:

- understands natural-language requests in any language supported by the configured AI model;
- searches only approved, live property listings in the application database;
- supports purchases, sales, long-term rentals and daily holiday stays;
- remembers filters such as city, budget, bedrooms and property type during a conversation;
- returns real listing cards directly inside the conversation;
- remains useful with deterministic database search when the OpenAI API is unavailable.

The OpenAI key is used only by Laravel and must never be exposed in the frontend.

```env
OPENAI_API_KEY=your_server_side_key
OPENAI_MODEL=gpt-5.4-mini
OPENAI_TIMEOUT=45
```

## Technology

- Frontend: Vue 3, TypeScript, Vite, Pinia, Tailwind CSS and PWA support
- Backend: Laravel 12, PHP 8.2, Sanctum and MySQL/SQLite
- AI: OpenAI Responses API with grounded property retrieval

## Local development

```bash
cd laravel
php artisan migrate
php artisan serve
```

```bash
cd frontend
npm install
npm run dev
```

Set `VITE_API_URL` in the frontend development environment if the API is not available under `/api/v1`.

## Quality checks

```bash
cd laravel && php artisan test
cd frontend && npm run build
```

The repository intentionally excludes environment files, API keys, database dumps, uploads, dependencies and production build archives.
