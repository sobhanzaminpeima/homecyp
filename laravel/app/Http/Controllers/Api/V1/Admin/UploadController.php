<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UploadController extends Controller
{
    /**
     * Store an uploaded image directly in the docroot (one level above the
     * laravel/ app folder) so it's served as a plain static file — no
     * storage:link or laravel/public involved, which matches this app's
     * "index.php lives outside laravel/public" hosting layout.
     */
    public function store(Request $request)
    {
        $request->validate([
            // Explicit mimes (not the bare "image" rule) so SVG is excluded —
            // SVG files are XML and can embed <script>, which would be stored
            // XSS the moment the file is served back from /uploads/.
            'file' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],
        ]);

        $docroot = dirname(base_path());
        $uploadsDir = $docroot . '/uploads';

        if (! is_dir($uploadsDir)) {
            mkdir($uploadsDir, 0755, true);
        }

        $file = $request->file('file');
        $filename = Str::random(24) . '.' . $file->getClientOriginalExtension();
        $file->move($uploadsDir, $filename);

        return response()->json([
            // Relative, not config('app.url') — an absolute URL bakes in whatever
            // host the app happened to be running on at upload time (e.g. a local
            // dev URL), which breaks the moment the DB is used anywhere else.
            'url' => '/uploads/' . $filename,
        ]);
    }
}
