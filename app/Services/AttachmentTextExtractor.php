<?php

namespace App\Services;

use Illuminate\Support\Str;

class AttachmentTextExtractor
{
    public function extract(string $path, string $mime): ?string
    {
        if (!is_file($path) || filesize($path) > 10 * 1024 * 1024) {
            return null;
        }

        if (str_starts_with($mime, 'text/') || in_array($mime, ['application/json', 'text/csv'], true)) {
            return Str::limit(trim((string) file_get_contents($path)), 12000, '');
        }

        if ($mime === 'application/pdf') {
            $raw = (string) file_get_contents($path);
            preg_match_all('/\(([^()]*(?:\\.[^()]*)*)\)\s*Tj/s', $raw, $matches);
            $text = implode(' ', array_map(fn ($value) => stripcslashes($value), $matches[1] ?? []));
            $text = trim(preg_replace('/\s+/u', ' ', $text) ?? '');
            return $text !== '' ? Str::limit($text, 12000, '') : null;
        }

        return null;
    }
}
