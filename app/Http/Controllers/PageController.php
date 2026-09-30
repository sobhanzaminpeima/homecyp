<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use App\Models\Page;

class PageController extends Controller
{
    public function about()
    {
        $page = Page::where('slug', 'about')->where('is_active', true)->first();
        return view('pages.about', compact('page'));
    }

    public function contact()
    {
        $page = Page::where('slug', 'contact')->where('is_active', true)->first();
        return view('pages.contact', compact('page'));
    }

    public function faq()
    {
        $faqs = Faq::where('is_active', true)
            ->with('translations')->orderBy('sort_order')->get()
            ->groupBy('category');

        return view('pages.faq', compact('faqs'));
    }

    public function investmentGuide()
    {
        return view('pages.investment-guide');
    }

    public function privacyPolicy()
    {
        return view('pages.privacy-policy');
    }

    public function terms()
    {
        return view('pages.terms');
    }
}
