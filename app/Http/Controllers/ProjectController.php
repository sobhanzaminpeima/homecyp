<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Property;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $query = Project::active()->with('translations');

        if ($request->filled('q')) {
            $term = $request->q;
            $query->where(function ($qq) use ($term) {
                $qq->where('location', 'like', "%{$term}%")
                   ->orWhere('developer', 'like', "%{$term}%")
                   ->orWhereHas('translations', fn ($t) => $t->where('title', 'like', "%{$term}%"));
            });
        }
        if ($request->filled('region')) {
            $query->where('region', $request->region);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('price_min')) {
            $query->where('price_from', '>=', $request->price_min);
        }
        if ($request->filled('price_max')) {
            $query->where('price_from', '<=', $request->price_max);
        }

        match ($request->get('sort', 'featured')) {
            'price_asc' => $query->orderBy('price_from', 'asc'),
            'price_desc' => $query->orderBy('price_from', 'desc'),
            'newest' => $query->latest(),
            default => $query->orderByDesc('is_featured')->latest(),
        };

        $projects = $query->paginate(9)->withQueryString();
        $regions = Project::active()->whereNotNull('region')->distinct()->pluck('region')->filter()->values();

        return view('projects.index', compact('projects', 'regions'));
    }

    public function show(string $slug)
    {
        $project = Project::where('slug', $slug)->active()
            ->with(['translations', 'properties.translations'])->firstOrFail();

        $project->increment('views');

        $seo = [
            'title' => $project->translation()?->meta_title ?: $project->translation()?->title,
            'description' => $project->translation()?->meta_description,
        ];

        return view('projects.show', compact('project', 'seo'));
    }
}
