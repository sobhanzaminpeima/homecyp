<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Property;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Response;

class SitemapController extends Controller
{
    public function index()
    {
        $xml = Cache::remember('sitemap_xml', 3600, function () {
            $urls = [];

            foreach ([
                ['route' => 'home', 'priority' => '1.0', 'freq' => 'daily'],
                ['route' => 'projects.index', 'priority' => '0.9', 'freq' => 'daily'],
                ['route' => 'resale.index', 'priority' => '0.9', 'freq' => 'daily'],
                ['route' => 'rentals.daily', 'priority' => '0.9', 'freq' => 'daily'],
                ['route' => 'rentals.longterm', 'priority' => '0.8', 'freq' => 'weekly'],
                ['route' => 'investment-guide', 'priority' => '0.7', 'freq' => 'monthly'],
                ['route' => 'about', 'priority' => '0.6', 'freq' => 'monthly'],
                ['route' => 'contact', 'priority' => '0.6', 'freq' => 'monthly'],
                ['route' => 'faq', 'priority' => '0.6', 'freq' => 'monthly'],
                ['route' => 'agents.index', 'priority' => '0.6', 'freq' => 'weekly'],
                ['route' => 'privacy-policy', 'priority' => '0.3', 'freq' => 'yearly'],
                ['route' => 'terms', 'priority' => '0.3', 'freq' => 'yearly'],
            ] as $page) {
                $urls[] = [
                    'loc' => route($page['route']),
                    'priority' => $page['priority'],
                    'freq' => $page['freq'],
                    'lastmod' => now()->toAtomString(),
                ];
            }

            Project::active()->select('slug', 'updated_at')->get()->each(function ($project) use (&$urls) {
                $urls[] = [
                    'loc' => route('projects.show', $project->slug),
                    'priority' => '0.8',
                    'freq' => 'weekly',
                    'lastmod' => $project->updated_at->toAtomString(),
                ];
            });

            Property::active()->select('slug', 'category', 'updated_at')->get()->each(function ($property) use (&$urls) {
                $route = match ($property->category) {
                    'daily_rental' => route('rentals.daily.show', $property->slug),
                    'long_term_rental' => route('rentals.longterm.show', $property->slug),
                    default => route('properties.show', $property->slug),
                };
                $urls[] = [
                    'loc' => $route,
                    'priority' => '0.7',
                    'freq' => 'weekly',
                    'lastmod' => $property->updated_at->toAtomString(),
                ];
            });

            return view('sitemap', compact('urls'))->render();
        });

        return Response::make($xml, 200, ['Content-Type' => 'application/xml']);
    }
}
