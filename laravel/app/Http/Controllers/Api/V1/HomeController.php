<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\AdvertisementResource;
use App\Http\Resources\BusinessResource;
use App\Models\Advertisement;
use App\Models\Business;
use App\Models\Setting;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $citySlug = $request->string('city')->toString();

        $featuredQuery = Business::query()->live()->featured()->with(['city', 'category']);
        $popularQuery = Business::query()->live()->with(['city', 'category']);
        $adsQuery = Advertisement::query()->running()->with('city');

        if ($citySlug) {
            $featuredQuery->whereHas('city', fn ($q) => $q->where('slug', $citySlug));
            $popularQuery->whereHas('city', fn ($q) => $q->where('slug', $citySlug));
            $adsQuery->where(function ($q) use ($citySlug) {
                $q->whereNull('city_id')->orWhereHas('city', fn ($cq) => $cq->where('slug', $citySlug));
            });
        }

        $viewsBase = (int) Setting::get('stats', 'views_base', 0);
        $viewsTotal = (int) Business::query()->sum('view_count');

        return response()->json([
            'featured' => BusinessResource::collection($featuredQuery->orderByDesc('rating_avg')->take(10)->get()),
            'popular' => BusinessResource::collection($popularQuery->orderByDesc('view_count')->take(10)->get()),
            'ads' => AdvertisementResource::collection($adsQuery->take(8)->get()),
            'view_counter' => $viewsBase + $viewsTotal,
            'counter_label' => Setting::get('stats', 'counter_label', 'people explored Easy Cyprus'),
        ]);
    }
}
