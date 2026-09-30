<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\BusinessProjectResource;
use App\Models\BusinessProject;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class DashboardProjectController extends Controller
{
    public function store(Request $request)
    {
        $business = $this->ownedBusiness($request);

        if ($business->category?->content_type !== 'projects') {
            abort(422, 'Your business category does not support project listings.');
        }

        $limit = $business->listingLimit();
        if ($limit !== null && $business->projects()->count() >= $limit) {
            throw ValidationException::withMessages([
                'limit' => ["You've reached your plan's limit of {$limit} listings. Upgrade your package to add more."],
            ]);
        }

        $data = $this->validated($request);
        $project = $business->projects()->create($data + ['status' => 'pending']);

        return new BusinessProjectResource($project);
    }

    public function update(Request $request, BusinessProject $project)
    {
        $this->authorizeProject($request, $project);

        $data = $this->validated($request);
        // Any edit sends it back to moderation, same as editing the business itself.
        $project->update($data + ['status' => 'pending']);

        return new BusinessProjectResource($project);
    }

    public function destroy(Request $request, BusinessProject $project)
    {
        $this->authorizeProject($request, $project);
        $project->delete();

        return response()->json(['message' => 'Project deleted.']);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'images' => ['nullable', 'array', 'max:5'],
            'images.*' => ['string'],
            'property_type' => ['required', Rule::in(['apartment', 'villa', 'land', 'commercial', 'other'])],
            'listing_type' => ['required', Rule::in(['sale', 'rent', 'presale'])],
            'rental_period' => ['nullable', Rule::in(['daily', 'monthly', 'long_term'])],
            'available_from' => ['nullable', 'date'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'size:3'],
            'area_m2' => ['nullable', 'integer', 'min:0'],
            'bedrooms' => ['nullable', 'integer', 'min:0', 'max:50'],
            'bathrooms' => ['nullable', 'integer', 'min:0', 'max:50'],
            'max_guests' => ['nullable', 'integer', 'min:1', 'max:100'],
            'minimum_stay' => ['nullable', 'integer', 'min:1', 'max:365'],
            'floor' => ['nullable', 'string', 'max:50'],
            'amenities' => ['nullable', 'array', 'max:40'],
            'amenities.*' => ['string', 'max:80'],
            'address' => ['nullable', 'string', 'max:255'],
            'booking_url' => ['nullable', 'url', 'max:255'],
            'lat' => ['nullable', 'numeric'],
            'lng' => ['nullable', 'numeric'],
        ]);
    }

    private function ownedBusiness(Request $request)
    {
        $business = $request->user()->businesses()->first();

        if (! $business) {
            throw ValidationException::withMessages(['business' => ['You do not have a business listing yet.']]);
        }

        return $business;
    }

    private function authorizeProject(Request $request, BusinessProject $project): void
    {
        if ($project->business->owner_id !== $request->user()->id) {
            abort(403);
        }
    }
}
