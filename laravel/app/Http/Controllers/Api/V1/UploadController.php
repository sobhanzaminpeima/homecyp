<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UploadController extends Controller
{
    /**
     * Image upload for authenticated business owners (logo / cover photo).
     * Mirrors Admin\UploadController — same docroot-relative /uploads/ layout.
     */
    public function store(Request $request)
    {
        $request->validate([
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
            'url' => '/uploads/' . $filename,
        ]);
    }
}
