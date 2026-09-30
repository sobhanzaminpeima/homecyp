<?php
/**
 * Easy Cyprus — web-based setup wizard.
 *
 * Runs on shared cPanel hosting with no SSH/terminal access. Writes laravel/.env,
 * generates APP_KEY, and runs migrate:fresh --seed via shell_exec/exec, falling
 * back to the guarded API route if shell execution is disabled on this host.
 *
 * Self-locks after the first successful run (laravel/storage/app/installed.lock).
 */

error_reporting(E_ALL & ~E_DEPRECATED);

$laravelDir = __DIR__ . '/laravel';
$lockFile = $laravelDir . '/storage/app/installed.lock';
$envTemplateFile = $laravelDir . '/.env.setup-template';
$envFile = $laravelDir . '/.env';

function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function render(string $title, string $body): void
{
    echo "<!doctype html><html lang=\"en\"><head><meta charset=\"utf-8\">"
        . "<meta name=\"viewport\" content=\"width=device-width, initial-scale=1\">"
        . "<title>{$title} — Easy Cyprus Setup</title>"
        . "<style>
            body{font-family:system-ui,sans-serif;background:#f4f7f8;color:#1a3557;max-width:560px;margin:40px auto;padding:0 20px;}
            .card{background:#fff;border-radius:16px;padding:32px;box-shadow:0 6px 24px -12px rgba(26,53,87,.18);}
            h1{font-size:20px;margin:0 0 4px;}
            p.sub{color:#6b7a90;font-size:14px;margin-top:0;}
            label{display:block;font-size:13px;font-weight:600;margin:16px 0 4px;}
            input{width:100%;box-sizing:border-box;padding:10px 12px;border:1px solid #e2e8f0;border-radius:10px;font-size:14px;}
            button{margin-top:24px;width:100%;padding:12px;border:0;border-radius:9999px;background:linear-gradient(135deg,#2a9d8f,#1e7a6e);color:#fff;font-weight:600;font-size:15px;cursor:pointer;}
            .error{background:#fef2f2;color:#b91c1c;padding:12px 16px;border-radius:10px;font-size:13px;margin-top:16px;white-space:pre-wrap;}
            .success{background:#f0fdf4;color:#15803d;padding:12px 16px;border-radius:10px;font-size:13px;margin-top:16px;}
            code{background:#f1f5f9;padding:2px 6px;border-radius:6px;}
            .creds{background:#f8fafc;border:1px dashed #cbd5e1;border-radius:10px;padding:16px;margin-top:16px;font-size:14px;}
        </style></head><body><div class=\"card\">{$body}</div></body></html>";
}

// --- Self-lock: refuse to run again once installed ---
if (is_file($lockFile)) {
    render('Already installed', '
        <h1>Easy Cyprus is already installed</h1>
        <p class="sub">Setup already completed on this server.</p>
        <div class="success">Nothing to do here. If you need to reinstall, delete
            <code>laravel/storage/app/installed.lock</code> via File Manager first (this wipes and reseeds the database).</div>
    ');
    exit;
}

if (!is_dir($laravelDir)) {
    render('Setup error', '<h1>Setup error</h1><p class="sub">The <code>laravel/</code> folder was not found next to this script. Re-extract the deployment zip and try again.</p>');
    exit;
}

$errors = [];
$successOutput = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dbHost = trim($_POST['db_host'] ?? '');
    $dbPort = trim($_POST['db_port'] ?? '3306');
    $dbName = trim($_POST['db_database'] ?? '');
    $dbUser = trim($_POST['db_username'] ?? '');
    $dbPass = (string) ($_POST['db_password'] ?? '');
    $appUrl = rtrim(trim($_POST['app_url'] ?? ''), '/');

    if ($dbHost === '' || $dbName === '' || $dbUser === '' || $appUrl === '') {
        $errors[] = 'Please fill in the database host, database name, database username, and app URL.';
    }

    // Test the DB connection before writing anything.
    if (!$errors) {
        if (function_exists('mysqli_connect')) {
            $mysqli = @mysqli_connect($dbHost, $dbUser, $dbPass, $dbName, (int) $dbPort);
            if (!$mysqli) {
                $errors[] = 'Could not connect to the database: ' . mysqli_connect_error();
            } else {
                mysqli_close($mysqli);
            }
        } elseif (extension_loaded('pdo_mysql')) {
            try {
                new PDO("mysql:host={$dbHost};port={$dbPort};dbname={$dbName}", $dbUser, $dbPass);
            } catch (Throwable $e) {
                $errors[] = 'Could not connect to the database: ' . $e->getMessage();
            }
        } else {
            $errors[] = 'Neither mysqli nor pdo_mysql is available on this server. Contact your host.';
        }
    }

    if (!$errors && !is_file($envTemplateFile)) {
        $errors[] = 'Missing laravel/.env.setup-template — the deployment zip may be corrupted.';
    }

    if (!$errors) {
        $appKey = 'base64:' . base64_encode(random_bytes(32));
        $setupToken = bin2hex(random_bytes(20));

        $env = file_get_contents($envTemplateFile);
        $env = strtr($env, [
            '{{APP_KEY}}' => $appKey,
            '{{APP_URL}}' => $appUrl,
            '{{DB_HOST}}' => $dbHost,
            '{{DB_PORT}}' => $dbPort,
            '{{DB_DATABASE}}' => $dbName,
            '{{DB_USERNAME}}' => $dbUser,
            '{{DB_PASSWORD}}' => $dbPass,
            '{{SETUP_TOKEN}}' => $setupToken,
        ]);

        if (@file_put_contents($envFile, $env) === false) {
            $errors[] = 'Could not write laravel/.env — check that the laravel/ folder is writable.';
        }
    }

    if (!$errors) {
        [$ranOk, $log] = runMigrateAndSeed($laravelDir, $appUrl, $setupToken);

        if ($ranOk) {
            @mkdir(dirname($lockFile), 0755, true);
            @file_put_contents($lockFile, gmdate('c'));
            $successOutput = $log;
        } else {
            $errors[] = "Could not run the database migration automatically.\n\n{$log}\n\n"
                . "You can also trigger it manually by visiting this URL once in your browser:\n"
                . h($appUrl) . '/api/v1/setup/' . h($setupToken);
        }
    }
}

/**
 * @return array{0: bool, 1: string}
 */
function runMigrateAndSeed(string $laravelDir, string $appUrl, string $setupToken): array
{
    $log = '';

    // 1. Try shell_exec with a few likely PHP binary names.
    if (function_exists('shell_exec') && !inDisabledFunctions('shell_exec')) {
        foreach (['php', 'php8.2', 'php8.3', '/usr/local/bin/php'] as $bin) {
            $cmd = escapeshellarg($bin) . ' ' . escapeshellarg($laravelDir . '/artisan')
                . ' migrate:fresh --seed --force 2>&1';
            $output = @shell_exec($cmd);
            $log .= "\$ {$bin} artisan migrate:fresh --seed --force\n" . ($output ?? '(no output)') . "\n";
            if (is_string($output) && (stripos($output, 'error') === false) && stripos($output, 'not recognized') === false) {
                return [true, $log];
            }
        }
    }

    // 2. Try exec() as a fallback.
    if (function_exists('exec') && !inDisabledFunctions('exec')) {
        foreach (['php', 'php8.2', 'php8.3'] as $bin) {
            $cmd = escapeshellarg($bin) . ' ' . escapeshellarg($laravelDir . '/artisan')
                . ' migrate:fresh --seed --force 2>&1';
            $outputLines = [];
            $exitCode = 1;
            @exec($cmd, $outputLines, $exitCode);
            $log .= "\$ {$bin} artisan migrate:fresh --seed --force (exec)\n" . implode("\n", $outputLines) . "\n";
            if ($exitCode === 0) {
                return [true, $log];
            }
        }
    }

    // 3. Fall back to the guarded HTTP setup route (self-locks server-side too).
    $url = $appUrl . '/api/v1/setup/' . $setupToken;

    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 120,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $log .= "GET {$url} (curl) -> HTTP {$httpCode}\n" . ($response ?: '(no response)') . "\n";
        if ($httpCode === 200) {
            return [true, $log];
        }
    } elseif (ini_get('allow_url_fopen')) {
        $response = @file_get_contents($url);
        $log .= "GET {$url} (file_get_contents)\n" . ($response ?: '(no response)') . "\n";
        if ($response !== false && stripos($response, 'Installation complete') !== false) {
            return [true, $log];
        }
    } else {
        $log .= "Neither curl nor allow_url_fopen is available to call the fallback setup route.\n";
    }

    return [false, $log];
}

function inDisabledFunctions(string $function): bool
{
    $disabled = array_map('trim', explode(',', (string) ini_get('disable_functions')));
    return in_array($function, $disabled, true);
}

if ($successOutput !== null) {
    render('Setup complete', '
        <h1>Easy Cyprus is installed</h1>
        <p class="sub">The database has been created and seeded with starter data.</p>
        <div class="success">Installation complete — this wizard is now locked.</div>
        <div class="creds">
            <strong>Admin login</strong><br>
            Email: <code>admin@easycyprus.com</code><br>
            Password: <code>Admin@2026!</code><br><br>
            <em>Change this password after your first login.</em>
        </div>
        <p class="sub">Visit your site\'s homepage to get started, and <code>/admin/login</code> for the admin panel.</p>
    ');
    exit;
}

$dbHostVal = h($_POST['db_host'] ?? 'localhost');
$dbPortVal = h($_POST['db_port'] ?? '3306');
$dbNameVal = h($_POST['db_database'] ?? '');
$dbUserVal = h($_POST['db_username'] ?? '');
$appUrlVal = h($_POST['app_url'] ?? (($_SERVER['REQUEST_SCHEME'] ?? 'https') . '://' . ($_SERVER['HTTP_HOST'] ?? '')));

$errorHtml = $errors ? '<div class="error">' . h(implode("\n\n", $errors)) . '</div>' : '';

render('Setup', "
    <h1>Set up Easy Cyprus</h1>
    <p class=\"sub\">Enter your MySQL database details from cPanel / phpMyAdmin. This runs once.</p>
    {$errorHtml}
    <form method=\"post\">
        <label>Database host</label>
        <input name=\"db_host\" value=\"{$dbHostVal}\" required>

        <label>Database port</label>
        <input name=\"db_port\" value=\"{$dbPortVal}\" required>

        <label>Database name</label>
        <input name=\"db_database\" value=\"{$dbNameVal}\" required>

        <label>Database username</label>
        <input name=\"db_username\" value=\"{$dbUserVal}\" required>

        <label>Database password</label>
        <input name=\"db_password\" type=\"password\">

        <label>App URL (this site's full URL, no trailing slash)</label>
        <input name=\"app_url\" value=\"{$appUrlVal}\" required>

        <button type=\"submit\">Install</button>
    </form>
");
