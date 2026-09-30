<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\BusinessResource;
use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class BusinessController extends Controller
{
    public function index(Request $request)
    {
        $query = Business::query()->live()->with(['city', 'category']);

        if ($request->filled('city')) {
            $query->whereHas('city', fn ($q) => $q->where('slug', $request->string('city')));
        }

        if ($request->filled('category')) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $request->string('category')));
        }

        if ($request->filled('q')) {
            $query->search($request->string('q'));
        }

        $lat = $request->filled('lat') ? $request->float('lat') : null;
        $lng = $request->filled('lng') ? $request->float('lng') : null;
        $sortByDistance = $request->string('sort')->toString() === 'distance' && $lat !== null && $lng !== null;
        $openNow = $request->boolean('open_now');
        $perPage = $request->integer('per_page', 15);

        if ($sortByDistance || $openNow) {
            // Distance is computed in PHP (see Business::distanceKm), so it can't be
            // an ORDER BY clause — pull every match (capped generously), sort, then
            // paginate the already-sorted collection by hand.
            if ($sortByDistance) $query->whereNotNull('lat')->whereNotNull('lng');
            $all = $query->limit(500)->get();

            $all = $all->map(function (Business $business) use ($lat, $lng, $sortByDistance) {
                if ($sortByDistance) $business->distance_km = Business::distanceKm($lat, $lng, (float) $business->lat, (float) $business->lng);
                return $business;
            });
            if ($openNow) $all = $all->filter(fn (Business $b) => $b->isOpenNow() === true);
            $all = ($sortByDistance ? $all->sortBy('distance_km') : $all->sortByDesc('rating_avg'))->values();

            $page = $request->integer('page', 1);
            $businesses = new LengthAwarePaginator(
                $all->forPage($page, $perPage),
                $all->count(),
                $perPage,
                $page,
                ['path' => $request->url(), 'query' => $request->query()]
            );
        } else {
            $businesses = $query->orderByDesc('is_featured')
                ->orderByDesc('rating_avg')
                ->paginate($perPage);
        }

        return BusinessResource::collection($businesses);
    }

    public function show(Request $request, string $slug)
    {
        $business = Business::query()
            ->live()
            ->with([
                'city',
                'category',
                'menus.items',
                'reviews' => fn ($q) => $q->approved()->with('user')->latest(),
                'projects' => fn ($q) => $q->approved()->latest(),
                'products' => fn ($q) => $q->where('is_available', true),
                'services' => fn ($q) => $q->where('is_available', true),
            ])
            ->where('slug', $slug)
            ->firstOrFail();

        $business->increment('view_count');

        if ($request->user()) {
            $business->is_favorited = $business->favoritedBy()->where('user_id', $request->user()->id)->exists();
        }

        return new BusinessResource($business);
    }

    public function similar(string $slug)
    {
        $business = Business::query()->live()->where('slug', $slug)->firstOrFail();

        $similar = Business::query()->live()
            ->with(['city', 'category'])
            ->where('category_id', $business->category_id)
            ->where('id', '!=', $business->id)
            ->orderByDesc('rating_avg')
            ->take(6)
            ->get();

        return BusinessResource::collection($similar);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $business = new Business($data);
        $business->owner_id = $request->user()->id;
        $business->slug = $this->uniqueSlug($data['name']);
        $business->status = 'pending';
        $business->save();

        return new BusinessResource($business->load(['city', 'category']));
    }

    public function update(Request $request, Business $business)
    {
        if ($business->owner_id !== $request->user()->id) {
            abort(403);
        }

        $data = $this->validated($request, $business->id);

        if ($data['name'] !== $business->name) {
            $business->slug = $this->uniqueSlug($data['name'], $business->id);
        }

        // Any edit by the owner sends the listing back to moderation so an
        // already-approved business can't be silently swapped for spam/scam
        // content while staying live.
        $business->fill($data);
        $business->status = 'pending';
        $business->save();

        return new BusinessResource($business->load(['city', 'category']));
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'city_id' => ['required', 'exists:cities,id'],
            'category_id' => ['required', 'exists:categories,id'],
            'description' => ['nullable', 'string'],
            'address' => ['nullable', 'string', 'max:255'],
            'lat' => ['nullable', 'numeric'],
            'lng' => ['nullable', 'numeric'],
            'phone' => ['nullable', 'string', 'max:50'],
            'whatsapp' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'website' => ['nullable', 'url', 'max:255'],
            'logo' => ['nullable', 'string', 'max:255'],
            'cover_image' => ['nullable', 'string', 'max:255'],
            'gallery' => ['nullable', 'array'],
            'social' => ['nullable', 'array'],
            'hours' => ['nullable', 'array'],
        ]);
    }

    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $slug = Str::slug($name);
        $original = $slug;
        $count = 1;

        while (Business::query()->where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = "{$original}-" . ++$count;
        }

        return $slug;
    }
}
