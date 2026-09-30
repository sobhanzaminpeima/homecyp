<?php

namespace App\Http\Controllers;

use App\Models\Property;
use Illuminate\Http\Request;

class PropertyController extends Controller
{
    private const SEO_AREAS = [
        'kyrenia' => 'Kyrenia', 'iskele' => 'Iskele', 'famagusta' => 'Famagusta', 'nicosia' => 'Nicosia',
    ];

    public function area(string $area)
    {
        abort_unless(isset(self::SEO_AREAS[$area]), 404);
        $region = self::SEO_AREAS[$area];
        $properties = Property::active()->where('region', 'like', "%{$region}%")
            ->with('translations')->latest()->paginate(12);

        return view('properties.area', compact('area', 'region', 'properties'));
    }

    public function index(Request $request)
    {
        $query = Property::active()->with('translations');
        $query = $this->applyFilters($query, $request);

        $properties = $query->paginate(12)->withQueryString();

        return view('properties.index', compact('properties'));
    }

    public function resale(Request $request)
    {
        $query = Property::active()->category('resale')->with('translations');
        $query = $this->applyFilters($query, $request);

        $properties = $query->paginate(12)->withQueryString();

        return view('properties.resale', compact('properties'));
    }

    public function show(string $slug)
    {
        $property = Property::where('slug', $slug)->active()
            ->with(['translations', 'agent', 'project'])->firstOrFail();

        $property->increment('views');

        $related = Property::active()
            ->where('id', '!=', $property->id)
            ->where('category', $property->category)
            ->with('translations')->take(4)->get();

        $seo = [
            'title' => $property->translation()?->meta_title ?: $property->translation()?->title,
            'description' => $property->translation()?->meta_description,
        ];

        return view('properties.show', compact('property', 'related', 'seo'));
    }

    public function search(Request $request)
    {
        $query = Property::active()->with('translations');
        $query = $this->applyFilters($query, $request);

        if ($request->expectsJson()) {
            return response()->json([
                'properties' => $query->paginate(12),
            ]);
        }

        $properties = $query->paginate(12)->withQueryString();
        return view('properties.search', compact('properties'));
    }

    private function applyFilters($query, Request $request)
    {
        if ($request->filled('location')) {
            $query->where('location', 'like', '%' . $request->location . '%');
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        if ($request->filled('bedrooms')) {
            $query->where('bedrooms', '>=', $request->bedrooms);
        }
        if ($request->filled('price_min')) {
            $query->where('price', '>=', $request->price_min);
        }
        if ($request->filled('price_max')) {
            $query->where('price', '<=', $request->price_max);
        }

        $sort = $request->get('sort', 'latest');
        match ($sort) {
            'price_asc' => $query->orderBy('price', 'asc'),
            'price_desc' => $query->orderBy('price', 'desc'),
            'oldest' => $query->orderBy('created_at', 'asc'),
            default => $query->orderBy('created_at', 'desc'),
        };

        return $query;
    }
}
