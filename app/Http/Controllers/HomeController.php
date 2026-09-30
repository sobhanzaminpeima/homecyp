<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\Project;
use App\Models\Testimonial;
use App\Models\Faq;
use App\Models\Post;
use App\Models\SiteSetting;

class HomeController extends Controller
{
    public function index()
    {
        // Admin → Homepage & Theme: hide every Airbnb listing from the homepage when off.
        $showAirbnb = SiteSetting::get('show_airbnb', '1') === '1';
        $hideAirbnb = fn ($query) => $showAirbnb ? $query : $query->where('is_airbnb', false);

        $featuredProjects = Project::featured()->active()
            ->with('translations')->latest()->take(6)->get();

        $featuredProperties = $hideAirbnb(Property::featured()->active())
            ->with('translations')->latest()->take(8)->get();

        // Tabbed listings by category for the homepage
        $tabbedListings = [
            'project' => $hideAirbnb(Property::active()->category('project'))->with('translations')->latest()->take(8)->get(),
            'resale' => $hideAirbnb(Property::active()->category('resale'))->with('translations')->latest()->take(8)->get(),
            'daily_rental' => $hideAirbnb(Property::active()->category('daily_rental'))->with('translations')->latest()->take(8)->get(),
            'long_term_rental' => $hideAirbnb(Property::active()->category('long_term_rental'))->with('translations')->latest()->take(8)->get(),
        ];

        $dailyRentals = $hideAirbnb(Property::active()->category('daily_rental'))
            ->with('translations')->latest()->take(6)->get();

        $testimonials = Testimonial::where('is_active', true)
            ->orderBy('sort_order')->take(8)->get();

        $faqs = Faq::where('is_active', true)
            ->where('category', 'general')
            ->with('translations')->orderBy('sort_order')->take(10)->get();

        $latestPosts = Post::published()->with('translations', 'media')
            ->latest('published_at')->latest()->take(3)->get();

        $stats = [
            'properties' => Property::active()->count(),
            'projects' => Project::active()->count(),
            'happy_clients' => SiteSetting::get('stat_happy_clients', '500+'),
            'years_experience' => SiteSetting::get('stat_years_experience', '10+'),
        ];

        return view('home', compact(
            'featuredProjects', 'featuredProperties', 'tabbedListings',
            'dailyRentals', 'testimonials', 'faqs', 'stats', 'latestPosts'
        ));
    }
}
