#!/usr/bin/env bash
#
# HomeCyp — build a cPanel-ready deployment package.
# Run locally (needs PHP, Composer, Node). Produces homecyp-deploy.zip
# containing everything to upload to cPanel (vendor/ and public/build/ included).
#
set -e

echo "==> Installing production PHP dependencies..."
composer install --optimize-autoloader --no-dev

echo "==> Building front-end assets..."
npm install
npm run build

echo "==> Clearing caches (ship clean; the /install step re-caches on the server)..."
php artisan optimize:clear

echo "==> Generating .env for production (if missing)..."
[ -f .env.production.example ] && cp -n .env.production.example .env.deploy.example || true

echo "==> Creating zip..."
rm -f homecyp-deploy.zip
zip -r homecyp-deploy.zip . \
  -x "node_modules/*" \
  -x ".git/*" \
  -x "tests/*" \
  -x "storage/installed.lock" \
  -x "homecyp-deploy.zip" \
  -x ".env" \
  > /dev/null

echo ""
echo "✓ Done: homecyp-deploy.zip"
echo "  Next: upload & extract on cPanel, set .env, then visit /install"
echo "  (See DEPLOYMENT.md for the full step-by-step.)"
