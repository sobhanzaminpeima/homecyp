<?php

namespace App\Http\Controllers;

use App\Http\Middleware\SetLocale;
use Illuminate\Http\Request;

class LanguageController extends Controller
{
    public function switch(Request $request, string $locale)
    {
        if (in_array($locale, SetLocale::SUPPORTED_LOCALES, true)) {
            session(['locale' => $locale]);
            app()->setLocale($locale);
        }

        return redirect()->back()->withHeaders([
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
        ]);
    }
}
