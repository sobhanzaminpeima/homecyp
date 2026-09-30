<?php
/**
 * One-time cache clearer for cPanel deployments — run this after uploading
 * an updated .blade.php (or any file cached by `optimize`) so the live site
 * picks up the change immediately instead of serving the old compiled view.
 *
 * Visit /clear-cache.php once after uploading updated files, then delete it
 * (or leave it — it's safe to leave, it only clears this project's own caches).
 */

header('Content-Type: text/plain');

require dirname(__DIR__).'/vendor/autoload.php';
$app = require dirname(__DIR__).'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

foreach (['view:clear', 'cache:clear', 'config:clear', 'route:clear'] as $command) {
    Illuminate\Support\Facades\Artisan::call($command);
    echo "{$command}: " . trim(Illuminate\Support\Facades\Artisan::output()) . "\n";
}

Illuminate\Support\Facades\Artisan::call('optimize');
echo "optimize: " . trim(Illuminate\Support\Facades\Artisan::output()) . "\n";

echo "\nDone. Reload the site — it will now use the updated files.\n";
