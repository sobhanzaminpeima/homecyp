<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetLocale
{
    public const SUPPORTED_LOCALES = ['en', 'tr', 'fa', 'ar', 'ru', 'de'];

    public function handle(Request $request, Closure $next)
    {
        try {
            $locale = session('locale');

            if (!$locale) {
                $locale = $request->getPreferredLanguage(self::SUPPORTED_LOCALES)
                    ?: config('app.locale', 'en');
            }
        } catch (\Throwable $e) {
            // Session store may be unavailable during a fresh install — fall back safely.
            $locale = config('app.locale', 'en');
        }

        if (!in_array($locale, self::SUPPORTED_LOCALES, true)) {
            $locale = 'en';
        }

        app()->setLocale($locale);

        return $next($request);
    }
}
