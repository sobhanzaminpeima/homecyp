<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\CityResource;
use App\Models\City;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CityController extends Controller
{
    public function index()
    {
        return CityResource::collection(City::query()->orderBy('sort_order')->get());
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['slug'] = $this->uniqueSlug($data['name']);

        return new CityResource(City::query()->create($data));
    }

    public function update(Request $request, City $city)
    {
        $data = $this->validated($request);

        if ($data['name'] !== $city->name) {
            $data['slug'] = $this->uniqueSlug($data['name'], $city->id);
        }

        $city->update($data);

        return new CityResource($city);
    }

    public function destroy(City $city)
    {
        $city->delete();

        return response()->json(['message' => 'City deleted.']);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'name_tr' => ['nullable', 'string', 'max:255'],
            'name_fa' => ['nullable', 'string', 'max:255'],
            'image' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer'],
            'is_active' => ['boolean'],
        ]);
    }

    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $slug = Str::slug($name);
        $original = $slug;
        $count = 1;

        while (City::query()->where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = "{$original}-" . ++$count;
        }

        return $slug;
    }
}
