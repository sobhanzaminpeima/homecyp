<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\BusinessResource;
use App\Models\Business;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function index(Request $request)
    {
        $businesses = Business::query()->live()
            ->with(['city', 'category'])
            ->whereHas('favoritedBy', fn ($q) => $q->where('user_id', $request->user()->id))
            ->get();

        return BusinessResource::collection($businesses);
    }

    public function toggle(Request $request, string $slug)
    {
        $business = Business::query()->live()->where('slug', $slug)->firstOrFail();

        $favorite = $business->favoritedBy()->where('user_id', $request->user()->id)->first();

        if ($favorite) {
            $favorite->delete();

            return response()->json(['favorited' => false]);
        }

        $business->favoritedBy()->create(['user_id' => $request->user()->id]);

        return response()->json(['favorited' => true]);
    }
}
