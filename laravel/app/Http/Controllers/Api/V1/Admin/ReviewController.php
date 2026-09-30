<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ReviewResource;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ReviewController extends Controller
{
    public function index(Request $request)
    {
        $query = Review::query()->with(['user', 'business']);

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        return ReviewResource::collection($query->latest()->paginate($request->integer('per_page', 20)));
    }

    public function update(Request $request, Review $review)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['pending', 'approved', 'rejected'])],
        ]);

        $review->update($data);
        $review->business->recalculateRating();

        return new ReviewResource($review->load(['user', 'business']));
    }

    public function destroy(Review $review)
    {
        $business = $review->business;
        $review->delete();
        $business->recalculateRating();

        return response()->json(['message' => 'Review deleted.']);
    }
}
