# Security policy

## Reporting

Report vulnerabilities privately to the repository owner. Do not open a public issue containing credentials, lead data, exploitable URLs, or production reproduction data.

## Secret handling

- Never commit `.env`, exports, server credentials, API keys, SMTP passwords, or integration tokens.
- Use example env files only for empty placeholders.
- Rotate any secret exposed in Git history, logs, screenshots, or chat.
- Production must use `APP_DEBUG=false` and HTTPS.

## Application controls

- Chat sends, sign-in, and password reset are rate limited.
- Sessions regenerate after lead sign-in.
- Sign-out starts a fresh anonymous conversation on shared devices.
- Property cards are restricted to server-validated inventory IDs.
- Passwords are hashed and reset tokens expire.
- Uploads are limited by type and size.
- CRM requests can use a bearer token.

## Production setup

Set unique `ADMIN_EMAIL` and `ADMIN_PASSWORD` values before the first seed. Rotate bootstrap credentials after first sign-in. Restrict backups and `storage/`, keep dependencies current, and review logs without exposing lead data.
