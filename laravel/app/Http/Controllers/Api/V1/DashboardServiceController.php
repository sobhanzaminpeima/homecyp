<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\BusinessServiceResource;
use App\Models\BusinessService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class DashboardServiceController extends Controller
{
    public function store(Request $request)
    {
        $business = $this->ownedBusiness($request);

        if ($business->category?->content_type !== 'services') {
            abort(422, 'Your business category does not support a service list.');
        }

        $limit = $business->listingLimit();
        if ($limit !== null && $business->services()->count() >= $limit) {
            throw ValidationException::withMessages([
                'limit' => ["You've reached your plan's limit of {$limit} listings. Upgrade your package to add more."],
            ]);
        }

        $service = $business->services()->create($this->validated($request));

        return new BusinessServiceResource($service);
    }

    public function update(Request $request, BusinessService $service)
    {
        $this->authorizeService($request, $service);
        $service->update($this->validated($request));

        return new BusinessServiceResource($service);
    }

    public function destroy(Request $request, BusinessService $service)
    {
        $this->authorizeService($request, $service);
        $service->delete();

        return response()->json(['message' => 'Service deleted.']);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'duration_minutes' => ['nullable', 'integer', 'min:0', 'max:1440'],
            'image' => ['nullable', 'string', 'max:255'],
            'is_available' => ['boolean'],
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

    private function authorizeService(Request $request, BusinessService $service): void
    {
        if ($service->business->owner_id !== $request->user()->id) {
            abort(403);
        }
    }
}
