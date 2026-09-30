<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

class SetupController extends Controller
{
    public function run(string $token)
    {
        if (Storage::disk('local')->exists('installed.lock')) {
            return response()->json([
                'message' => 'This application has already been installed. Delete storage/app/installed.lock to reinstall.',
            ], 403);
        }

        $expected = config('app.setup_token');

        if (! $expected || ! hash_equals((string) $expected, $token)) {
            abort(403, 'Invalid setup token.');
        }

        Artisan::call('migrate:fresh', ['--seed' => true, '--force' => true]);

        Storage::disk('local')->put('installed.lock', now()->toIso8601String());

        return response()->json([
            'message' => 'Installation complete.',
            'admin_email' => 'admin@easycyprus.com',
            'admin_password' => 'Admin@2026!',
        ]);
    }
}
