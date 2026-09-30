<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

/**
 * Browser-based installer for cPanel hosting without terminal/SSH access.
 * Runs migrations, seeders, storage link and caching from the web.
 * Self-locks after a successful install by writing storage/installed.lock.
 */
class InstallerController extends Controller
{
    protected function lockFile(): string
    {
        return storage_path('installed.lock');
    }

    protected function isInstalled(): bool
    {
        return File::exists($this->lockFile());
    }

    public function index()
    {
        if ($this->isInstalled()) {
            return view('installer.done', ['alreadyInstalled' => true]);
        }

        $checks = $this->requirements();
        return view('installer.index', compact('checks'));
    }

    protected function lockingFile(): string
    {
        return storage_path('installing.lock');
    }

    public function run(Request $request)
    {
        if ($this->isInstalled()) {
            return redirect()->route('installer.index');
        }

        // Guards against a double-click/double-submit on "Install HomeCyp Now"
        // racing two concurrent `migrate` runs against the same fresh database
        // (one creates a table, the other fails with "already exists").
        if (File::exists($this->lockingFile()) && File::lastModified($this->lockingFile()) > now()->subMinutes(2)->timestamp) {
            return view('installer.index', [
                'checks' => $this->requirements(),
                'errors' => [['step' => 'Error', 'ok' => false, 'msg' => 'An installation is already in progress (started less than 2 minutes ago). Please wait for it to finish before trying again.']],
            ]);
        }
        File::put($this->lockingFile(), now()->toDateTimeString());

        $output = [];

        try {
            // 0. Fix file/directory permissions. Zips built on Windows (this project
            // ships from a Windows dev machine) don't preserve Unix permission bits
            // reliably — some directories can extract on Linux hosts as 0644 (no
            // execute/traverse bit), silently blocking everything beneath them
            // ("Permission denied" on files that visibly exist). Doing this first,
            // before autoload-dependent code runs, avoids needing a one-off script.
            $fixed = $this->fixPermissions();
            $output[] = ['step' => 'Fix file permissions', 'ok' => true, 'msg' => "{$fixed['dirs']} directories, {$fixed['files']} files checked"];

            // 1. Test DB connection
            DB::connection()->getPdo();
            $output[] = ['step' => 'Database connection', 'ok' => true, 'msg' => 'Connected to ' . config('database.connections.' . config('database.default') . '.database')];

            // 2. Migrate
            Artisan::call('migrate', ['--force' => true]);
            $output[] = ['step' => 'Database migrations', 'ok' => true, 'msg' => trim(Artisan::output())];

            // 3. Seed (idempotent — uses firstOrCreate)
            Artisan::call('db:seed', ['--force' => true]);
            $output[] = ['step' => 'Seed initial data', 'ok' => true, 'msg' => 'Roles, admin user, settings, FAQs and testimonials created'];

            // 4. Storage link (best-effort; may already exist or be blocked on some hosts)
            try {
                Artisan::call('storage:link');
                $output[] = ['step' => 'Storage link', 'ok' => true, 'msg' => trim(Artisan::output()) ?: 'Linked'];
            } catch (\Throwable $e) {
                $output[] = ['step' => 'Storage link', 'ok' => false, 'msg' => 'Skipped — create the public/storage symlink manually or via cPanel if images do not show.'];
            }

            // 5. Cache config/routes/views for performance
            Artisan::call('optimize');
            $output[] = ['step' => 'Optimize (cache config/routes/views)', 'ok' => true, 'msg' => 'Cached'];

            // 6. Lock the installer
            File::put($this->lockFile(), 'Installed at ' . now()->toDateTimeString());
            $output[] = ['step' => 'Lock installer', 'ok' => true, 'msg' => 'Installer disabled for security'];

            File::delete($this->lockingFile());
            session()->flash('install_output', $output);
            return redirect()->route('installer.done');
        } catch (\Throwable $e) {
            File::delete($this->lockingFile());
            $output[] = ['step' => 'Error', 'ok' => false, 'msg' => $e->getMessage()];
            return view('installer.index', [
                'checks' => $this->requirements(),
                'errors' => $output,
            ]);
        }
    }

    public function done()
    {
        return view('installer.done', [
            'output' => session('install_output', []),
            'alreadyInstalled' => $this->isInstalled() && !session()->has('install_output'),
        ]);
    }

    /**
     * Recursively normalizes permissions under the project root, chmod'ing
     * each directory to 0755 before descending into it (a non-traversable
     * directory can't be scanned at all until its own bit is fixed first —
     * see DEPLOYMENT.md for why this matters on Windows-built zips).
     *
     * Note: this only helps folders Laravel doesn't need until *after* it
     * boots (storage/, resources/, etc.). If vendor/ itself is corrupted,
     * the app can't boot to reach this code at all — that case needs the
     * standalone public/fix-permissions.php run before /install.
     */
    protected function fixPermissions(): array
    {
        $dirCount = 0;
        $fileCount = 0;

        $walk = function (string $path) use (&$walk, &$dirCount, &$fileCount) {
            @chmod($path, 0755);
            $dirCount++;

            $entries = @scandir($path);
            if ($entries === false) {
                return;
            }

            foreach ($entries as $entry) {
                if ($entry === '.' || $entry === '..') continue;
                $full = $path.'/'.$entry;

                if (is_dir($full)) {
                    $walk($full);
                } else {
                    @chmod($full, 0644);
                    $fileCount++;
                }
            }
        };

        foreach (['storage', 'bootstrap/cache', 'resources', 'routes', 'lang', 'app', 'config', 'database'] as $dir) {
            $full = base_path($dir);
            if (is_dir($full)) {
                $walk($full);
            }
        }

        @chmod(storage_path(), 0775);
        @chmod(base_path('bootstrap/cache'), 0775);

        return ['dirs' => $dirCount, 'files' => $fileCount];
    }

    protected function requirements(): array
    {
        return [
            ['name' => 'PHP >= 8.2', 'ok' => version_compare(PHP_VERSION, '8.2.0', '>=')],
            ['name' => 'PDO MySQL extension', 'ok' => extension_loaded('pdo_mysql')],
            ['name' => 'Mbstring extension', 'ok' => extension_loaded('mbstring')],
            ['name' => 'OpenSSL extension', 'ok' => extension_loaded('openssl')],
            ['name' => 'Intl extension', 'ok' => extension_loaded('intl')],
            ['name' => 'GD or Imagick extension', 'ok' => extension_loaded('gd') || extension_loaded('imagick')],
            ['name' => 'Fileinfo extension', 'ok' => extension_loaded('fileinfo')],
            ['name' => 'storage/ writable', 'ok' => is_writable(storage_path())],
            ['name' => 'bootstrap/cache writable', 'ok' => is_writable(base_path('bootstrap/cache'))],
            ['name' => '.env configured', 'ok' => File::exists(base_path('.env'))],
        ];
    }
}
