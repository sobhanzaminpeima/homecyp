<?php
/**
 * HomeCyp — Storage link fixer for cPanel (no terminal needed).
 *
 * WHY: property/project images live in  storage/app/public  and are served via
 * the  public/storage  symlink. When the project is zipped on Windows that symlink
 * breaks on Linux, so images return 404. This script recreates it — and if the host
 * blocks symlinks, it copies the files instead.
 *
 * HOW: upload this file into your site's public folder (the one that contains
 * index.php), then open  https://yourdomain.com/fix-storage.php  in a browser.
 * DELETE it afterwards.
 */

header('Content-Type: text/plain; charset=utf-8');

// This file lives in the app's "public" dir. The Laravel base is one level up.
$publicDir = __DIR__;
$baseDir   = dirname($publicDir);

$target = $baseDir . '/storage/app/public'; // real files
$link   = $publicDir . '/storage';          // what the web serves as /storage

echo "HomeCyp storage fixer\n";
echo "=====================\n";
echo "Public dir : {$publicDir}\n";
echo "Storage src: {$target}\n";
echo "Link path  : {$link}\n\n";

if (!is_dir($target)) {
    echo "ERROR: source folder not found: {$target}\n";
    echo "Make sure you uploaded the whole project (storage/app/public must exist).\n";
    exit;
}

// Remove any broken existing link/dir first
if (is_link($link)) {
    @unlink($link);
    echo "Removed existing (broken) symlink.\n";
} elseif (is_dir($link)) {
    echo "A real 'storage' folder already exists in public/. Will refresh its contents by copy.\n";
}

// 1) Try a proper symlink (best option)
$linked = false;
if (!file_exists($link)) {
    $linked = @symlink($target, $link);
    if ($linked) {
        echo "\nSUCCESS: created symlink public/storage -> storage/app/public\n";
    }
}

// 2) Fallback: recursively copy the files (works even when symlinks are disabled)
if (!$linked) {
    echo "\nSymlink not available on this host — copying files instead...\n";
    if (!is_dir($link)) {
        @mkdir($link, 0755, true);
    }
    $count = copyRecursive($target, $link);
    echo "Copied {$count} file(s) into public/storage.\n";
    echo "(Re-run this script after adding new images in the admin.)\n";
}

echo "\nDone. Open your homepage — images should now load.\n";
echo "IMPORTANT: delete this fix-storage.php file now for security.\n";

function copyRecursive(string $src, string $dst): int
{
    $count = 0;
    $items = scandir($src);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }
        $from = $src . '/' . $item;
        $to   = $dst . '/' . $item;
        if (is_dir($from)) {
            if (!is_dir($to)) {
                @mkdir($to, 0755, true);
            }
            $count += copyRecursive($from, $to);
        } else {
            if (@copy($from, $to)) {
                $count++;
            }
        }
    }
    return $count;
}
