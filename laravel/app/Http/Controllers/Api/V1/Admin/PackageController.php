<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\PackageResource;
use App\Models\Package;
use Illuminate\Http\Request;

class PackageController extends Controller
{
    public function index()
    {
        return PackageResource::collection(Package::query()->orderBy('sort_order')->get());
    }

    public function store(Request $request)
    {
        return new PackageResource(Package::query()->create($this->validated($request)));
    }

    public function update(Request $request, Package $package)
    {
        $package->update($this->validated($request));

        return new PackageResource($package);
    }

    public function destroy(Package $package)
    {
        $package->delete();

        return response()->json(['message' => 'Package deleted.']);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'duration_days' => ['required', 'integer', 'min:1'],
            'is_featured' => ['boolean'],
            'is_premium' => ['boolean'],
            'listing_limit' => ['nullable', 'integer', 'min:0'],
            'features' => ['nullable', 'array'],
            'is_active' => ['boolean'],
            'sort_order' => ['nullable', 'integer'],
        ]);
    }
}
