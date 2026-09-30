<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\BusinessResource;
use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class BusinessController extends Controller
{
    public function index(Request $request)
    {
        $query = Business::query()->with(['city', 'category', 'owner']);

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('city_id')) {
            $query->where('city_id', $request->integer('city_id'));
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->integer('category_id'));
        }

        if ($request->filled('q')) {
            $term = $request->string('q');
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('phone', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%")
                    ->orWhere('address', 'like', "%{$term}%");
            });
        }

        return BusinessResource::collection($query->latest()->paginate($request->integer('per_page', 20)));
    }

    public function show(Business $business)
    {
        return new BusinessResource($business->load(['city', 'category', 'owner', 'menus.items']));
    }

    protected function rules(bool $creating): array
    {
        $sometimes = $creating ? 'required' : 'sometimes';

        return [
            'name' => [$sometimes, 'string', 'max:255'],
            'city_id' => [$sometimes, 'exists:cities,id'],
            'category_id' => [$sometimes, 'exists:categories,id'],
            'description' => ['sometimes', 'nullable', 'string'],
            'address' => ['sometimes', 'nullable', 'string', 'max:255'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:50'],
            'whatsapp' => ['sometimes', 'nullable', 'string', 'max:50'],
            'email' => ['sometimes', 'nullable', 'email', 'max:255'],
            'website' => ['sometimes', 'nullable', 'url', 'max:255'],
            'website_secondary' => ['sometimes', 'nullable', 'url', 'max:255'],
            'notes' => ['sometimes', 'nullable', 'string'],
            // rating_avg/rating_count are derived (external rating blended with
            // real reviews) — admins edit the external base rating instead, and
            // update() recomputes the displayed rating_avg/rating_count from it.
            'external_rating_avg' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:5'],
            'external_rating_count' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'status' => ['sometimes', Rule::in(['pending', 'approved', 'rejected', 'expired'])],
            'is_verified' => ['sometimes', 'boolean'],
            'is_featured' => ['sometimes', 'boolean'],
            'package_id' => ['sometimes', 'nullable', 'exists:packages,id'],
            'expire_at' => ['sometimes', 'nullable', 'date'],
        ];
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules(creating: true));

        $data['slug'] = $this->uniqueSlug($data['name']);
        $data['status'] ??= 'pending';

        $business = Business::query()->create($data);
        $business->recalculateRating();

        return new BusinessResource($business->load(['city', 'category']));
    }

    public function update(Request $request, Business $business)
    {
        $data = $request->validate($this->rules(creating: false));

        $business->update($data);

        if (array_key_exists('external_rating_avg', $data) || array_key_exists('external_rating_count', $data)) {
            $business->recalculateRating();
        }

        return new BusinessResource($business->fresh(['city', 'category']));
    }

    public function destroy(Business $business)
    {
        $business->delete();

        return response()->json(['message' => 'Business deleted.']);
    }

    protected function uniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i = 2;
        while (Business::query()->where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }
}
