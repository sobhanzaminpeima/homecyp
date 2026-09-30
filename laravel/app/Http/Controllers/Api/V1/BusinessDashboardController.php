<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\BusinessResource;
use Illuminate\Http\Request;

class BusinessDashboardController extends Controller
{
    public function show(Request $request)
    {
        $business = $request->user()->businesses()
            ->with(['city', 'category', 'package', 'menus.items', 'projects', 'products', 'services'])
            ->first();

        if (! $business) {
            return response()->json(['business' => null]);
        }

        return new BusinessResource($business);
    }

    public function stats(Request $request)
    {
        $business = $request->user()->businesses()->first();

        if (! $business) {
            return response()->json(['message' => 'No business listing yet.'], 404);
        }

        return response()->json([
            'view_count' => $business->view_count,
            'rating_avg' => (float) $business->rating_avg,
            'rating_count' => $business->rating_count,
            'favorites_count' => $business->favoritedBy()->count(),
            'status' => $business->status,
            'listing_limit' => $business->listingLimit(),
        ]);
    }
}
