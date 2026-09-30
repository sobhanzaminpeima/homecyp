<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ReviewResource;
use App\Models\Business;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(string $slug)
    {
        $business = Business::query()->live()->where('slug', $slug)->firstOrFail();

        $reviews = $business->reviews()->approved()->with('user')->latest()->paginate(10);

        return ReviewResource::collection($reviews);
    }

    public function store(Request $request, string $slug)
    {
        $business = Business::query()->live()->where('slug', $slug)->firstOrFail();

        $data = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'body' => ['nullable', 'string', 'max:2000'],
        ]);

        $review = $business->reviews()->updateOrCreate(
            ['user_id' => $request->user()->id],
            ['rating' => $data['rating'], 'body' => $data['body'] ?? null, 'status' => 'approved']
        );

        $business->recalculateRating();

        return new ReviewResource($review->load('user'));
    }

    public function destroy(Request $request, string $slug, Review $review)
    {
        $business = Business::query()->where('slug', $slug)->firstOrFail();

        if ($review->business_id !== $business->id || $review->user_id !== $request->user()->id) {
            abort(403);
        }

        $review->delete();
        $business->recalculateRating();

        return response()->json(['message' => 'Review deleted.']);
    }
}
