<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\AdvertisementResource;
use App\Models\Advertisement;
use Illuminate\Http\Request;

class AdvertisementController extends Controller
{
    public function index()
    {
        return AdvertisementResource::collection(Advertisement::query()->with('city')->orderBy('sort_order')->get());
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        return new AdvertisementResource(Advertisement::query()->create($data));
    }

    public function update(Request $request, Advertisement $advertisement)
    {
        $advertisement->update($this->validated($request));

        return new AdvertisementResource($advertisement);
    }

    public function destroy(Advertisement $advertisement)
    {
        $advertisement->delete();

        return response()->json(['message' => 'Advertisement deleted.']);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'image' => ['required', 'string', 'max:500'],
            'target_url' => ['nullable', 'string', 'max:500'],
            'placement' => ['nullable', 'string', 'max:100'],
            'city_id' => ['nullable', 'exists:cities,id'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date'],
            'is_active' => ['boolean'],
            'sort_order' => ['nullable', 'integer'],
        ]);
    }
}
