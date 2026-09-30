<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\BusinessProductResource;
use App\Models\BusinessProduct;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class DashboardProductController extends Controller
{
    public function store(Request $request)
    {
        $business = $this->ownedBusiness($request);

        if ($business->category?->content_type !== 'products') {
            abort(422, 'Your business category does not support a product catalog.');
        }

        $limit = $business->listingLimit();
        if ($limit !== null && $business->products()->count() >= $limit) {
            throw ValidationException::withMessages([
                'limit' => ["You've reached your plan's limit of {$limit} listings. Upgrade your package to add more."],
            ]);
        }

        $product = $business->products()->create($this->validated($request));

        return new BusinessProductResource($product);
    }

    public function update(Request $request, BusinessProduct $product)
    {
        $this->authorizeProduct($request, $product);
        $product->update($this->validated($request));

        return new BusinessProductResource($product);
    }

    public function destroy(Request $request, BusinessProduct $product)
    {
        $this->authorizeProduct($request, $product);
        $product->delete();

        return response()->json(['message' => 'Product deleted.']);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'price' => ['nullable', 'numeric', 'min:0'],
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

    private function authorizeProduct(Request $request, BusinessProduct $product): void
    {
        if ($product->business->owner_id !== $request->user()->id) {
            abort(403);
        }
    }
}
