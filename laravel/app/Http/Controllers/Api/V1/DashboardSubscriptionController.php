<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\SubscriptionRequestResource;
use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class DashboardSubscriptionController extends Controller
{
    public function index(Request $request)
    {
        $business = $this->ownedBusiness($request);

        $requests = $business->subscriptionRequests()->with('package')->latest()->get();

        return SubscriptionRequestResource::collection($requests);
    }

    public function store(Request $request)
    {
        $business = $this->ownedBusiness($request);

        $data = $request->validate([
            'package_id' => ['required', 'exists:packages,id'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        if ($business->subscriptionRequests()->where('status', 'pending')->exists()) {
            throw ValidationException::withMessages([
                'package_id' => ['You already have a pending subscription request.'],
            ]);
        }

        $package = Package::query()->findOrFail($data['package_id']);

        $subscriptionRequest = $business->subscriptionRequests()->create([
            'package_id' => $package->id,
            'note' => $data['note'] ?? null,
            'status' => 'pending',
        ]);

        return new SubscriptionRequestResource($subscriptionRequest->load('package'));
    }

    private function ownedBusiness(Request $request)
    {
        $business = $request->user()->businesses()->first();

        if (! $business) {
            throw ValidationException::withMessages(['business' => ['You do not have a business listing yet.']]);
        }

        return $business;
    }
}
