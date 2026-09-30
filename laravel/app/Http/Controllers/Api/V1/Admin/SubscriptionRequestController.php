<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\SubscriptionRequestResource;
use App\Models\SubscriptionRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class SubscriptionRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = SubscriptionRequest::query()->with(['business', 'package']);

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        return SubscriptionRequestResource::collection($query->latest()->paginate($request->integer('per_page', 20)));
    }

    public function update(Request $request, SubscriptionRequest $subscriptionRequest)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['approved', 'rejected'])],
        ]);

        $subscriptionRequest->update($data);

        if ($data['status'] === 'approved') {
            $package = $subscriptionRequest->package;
            $business = $subscriptionRequest->business;

            $business->update([
                'package_id' => $package->id,
                // Extend from "now" (not from the current expire_at) — a business
                // renewing early doesn't lose the remainder of an old cycle, but
                // this is still the simplest correct behavior for a first pass.
                'expire_at' => Carbon::now()->addDays($package->duration_days),
            ]);
        }

        return new SubscriptionRequestResource($subscriptionRequest->load(['business', 'package']));
    }
}
