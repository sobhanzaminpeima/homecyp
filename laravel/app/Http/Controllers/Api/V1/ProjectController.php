<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\BusinessProjectResource;
use App\Models\BusinessProject;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $query = BusinessProject::query()
            ->approved()
            ->whereHas('business', fn ($q) => $q->live())
            ->with(['business.city', 'business.category']);

        if ($request->filled('listing_type')) {
            $query->where('listing_type', $request->string('listing_type'));
        }

        if ($request->filled('rental_period')) {
            $query->where('rental_period', $request->string('rental_period'));
        }

        if ($request->filled('property_type')) {
            $query->where('property_type', $request->string('property_type'));
        }

        if ($request->filled('city')) {
            $query->whereHas('business.city', fn ($q) => $q->where('slug', $request->string('city')));
        }

        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->float('min_price'));
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->float('max_price'));
        }

        if ($request->filled('min_bedrooms')) {
            $query->where('bedrooms', '>=', $request->integer('min_bedrooms'));
        }

        $projects = $query->orderByDesc('is_featured')->latest()->paginate($request->integer('per_page', 15));

        return BusinessProjectResource::collection($projects);
    }

    public function show(BusinessProject $project)
    {
        abort_unless($project->status === 'approved' && $project->business->status === 'approved', 404);

        $project->increment('view_count');
        $project->load(['business.city', 'business.category']);

        return new BusinessProjectResource($project);
    }
}
