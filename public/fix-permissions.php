<?php
/**
 * One-time permission fixer for cPanel deployments — run this BEFORE visiting
 * /install if the site shows a fatal "vendor/autoload.php ... Permission
 * denied" error. Windows-built deploy zips don't preserve Unix permission
 * bits reliably; some directories can extract as 0644 (missing the execute/
 * traverse bit), which silently blocks everything beneath them even though
 * the files visibly exist.
 *
 * This lives at public/fix-permissions.php (not app code) specifically
 * because it must work WITHOUT Composer's autoloader — the exact case where
 * vendor/ itself is what's broken, so nothing that depends on the framework
 * booting can run yet. InstallerController::fixPermissions() covers the same
 * class of bug for storage/ and other app folders once Laravel does boot.
 *
 * Visit /fix-permissions.php once, confirm the output, then delete this file
 * (or leave it — it's safe to leave since it only touches its own project
 * folders and does nothing without being visited directly).
 */

header('Content-Type: text/plain');

$root = dirname(__DIR__);

$dirCount = 0;
$fileCount = 0;
$errors = [];

function fixDir(string $path, int &$dirCount, int &$fileCount, array &$errors): void
{
    if (!@chmod($path, 0755)) {
        $errors[] = "Could not chmod dir: {$path}";
    } else {
        $dirCount++;
    }

    $entries = @scandir($path);
    if ($entries === false) {
        $errors[] = "Could not scan dir even after chmod: {$path}";
        return;
    }

    foreach ($entries as $entry) {
        if ($entry === '.' || $entry === '..') continue;
        $full = $path.'/'.$entry;

        if (is_dir($full)) {
            fixDir($full, $dirCount, $fileCount, $errors);
        } elseif (@chmod($full, 0644)) {
            $fileCount++;
        } else {
            $errors[] = "Could not chmod file: {$full}";
        }
    }
}

foreach (['vendor', 'storage', 'bootstrap', 'app', 'config', 'database', 'resources', 'routes', 'lang', 'public'] as $top) {
    $full = $root.'/'.$top;
    if (!is_dir($full)) {
        echo "SKIP (not found): {$top}\n";
        continue;
    }
    fixDir($full, $dirCount, $fileCount, $errors);
    echo "Processed: {$top}\n";
}

foreach (['storage', 'bootstrap/cache'] as $top) {
    $full = $root.'/'.$top;
    if (is_dir($full)) {
        @chmod($full, 0775);
    }
}

echo "\nTotal directories fixed: {$dirCount}\n";
echo "Total files fixed: {$fileCount}\n";
echo "Errors: ".count($errors)."\n";
foreach (array_slice($errors, 0, 30) as $e) {
    echo "  - {$e}\n";
}

echo "\nDone. You can now visit /install. This file is safe to leave in place\n";
echo "(it does nothing unless visited directly), or delete it if you prefer.\n";
