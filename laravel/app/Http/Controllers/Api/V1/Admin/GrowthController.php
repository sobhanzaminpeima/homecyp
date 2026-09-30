<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Deal;
use App\Models\LocalEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GrowthController extends Controller
{
    public function index()
    {
        return response()->json([
            'counts' => [
                'claims' => DB::table('business_claims')->where('status', 'pending')->count(),
                'reports' => DB::table('business_reports')->where('status', 'pending')->count(),
                'inquiries' => DB::table('business_inquiries')->where('status', 'new')->count(),
                'events_7d' => DB::table('activity_events')->where('created_at', '>=', now()->subDays(7))->count(),
            ],
            'claims' => DB::table('business_claims')->join('businesses', 'businesses.id', '=', 'business_claims.business_id')->join('users', 'users.id', '=', 'business_claims.user_id')->select('business_claims.*', 'businesses.name as business_name', 'users.name as user_name', 'users.email')->latest('business_claims.created_at')->limit(50)->get(),
            'reports' => DB::table('business_reports')->join('businesses', 'businesses.id', '=', 'business_reports.business_id')->select('business_reports.*', 'businesses.name as business_name')->latest('business_reports.created_at')->limit(50)->get(),
            'inquiries' => DB::table('business_inquiries')->join('businesses', 'businesses.id', '=', 'business_inquiries.business_id')->select('business_inquiries.*', 'businesses.name as business_name')->latest('business_inquiries.created_at')->limit(50)->get(),
            'deals' => Deal::latest()->limit(50)->get(),
            'events' => LocalEvent::latest('starts_at')->limit(50)->get(),
        ]);
    }

    public function claim(Request $request, int $id)
    {
        $data = $request->validate(['status' => 'required|in:approved,rejected']);
        $claim = DB::table('business_claims')->where('id', $id)->first();
        abort_unless($claim, 404);
        DB::transaction(function () use ($claim, $data) {
            DB::table('business_claims')->where('id', $claim->id)->update([...$data, 'reviewed_at' => now(), 'updated_at' => now()]);
            if ($data['status'] === 'approved') {
                DB::table('businesses')->where('id', $claim->business_id)->update(['owner_id' => $claim->user_id, 'is_verified' => true, 'last_verified_at' => now(), 'updated_at' => now()]);
            }
            DB::table('app_notifications')->insert(['user_id' => $claim->user_id, 'title' => 'Business claim '.$data['status'], 'body' => 'Your ownership request has been reviewed.', 'url' => '/profile', 'created_at' => now(), 'updated_at' => now()]);
        });
        return response()->json(['message' => 'Claim updated.']);
    }

    public function report(Request $request, int $id)
    {
        $data = $request->validate(['status' => 'required|in:resolved,dismissed']);
        DB::table('business_reports')->where('id', $id)->update([...$data, 'reviewed_at' => now(), 'updated_at' => now()]);
        return response()->json(['message' => 'Report updated.']);
    }

    public function inquiry(Request $request, int $id)
    {
        $data = $request->validate(['status' => 'required|in:new,contacted,closed']);
        DB::table('business_inquiries')->where('id', $id)->update([...$data, 'updated_at' => now()]);
        return response()->json(['message' => 'Inquiry updated.']);
    }

    public function storeDeal(Request $request)
    {
        $data = $request->validate(['business_id' => 'required|exists:businesses,id', 'title' => 'required|string|max:160', 'description' => 'nullable|string|max:1500', 'discount_label' => 'nullable|string|max:80', 'code' => 'nullable|string|max:80', 'starts_at' => 'nullable|date', 'ends_at' => 'nullable|date|after_or_equal:starts_at', 'is_active' => 'boolean']);
        return response()->json(Deal::create($data), 201);
    }

    public function destroyDeal(Deal $deal) { $deal->delete(); return response()->noContent(); }

    public function storeEvent(Request $request)
    {
        $data = $request->validate(['city_id' => 'nullable|exists:cities,id', 'business_id' => 'nullable|exists:businesses,id', 'title' => 'required|string|max:160', 'description' => 'nullable|string|max:2000', 'venue' => 'nullable|string|max:180', 'image' => 'nullable|string|max:255', 'booking_url' => 'nullable|url|max:255', 'starts_at' => 'required|date', 'ends_at' => 'nullable|date|after_or_equal:starts_at', 'price' => 'nullable|numeric|min:0', 'currency' => 'nullable|string|size:3', 'status' => 'nullable|in:draft,published']);
        return response()->json(LocalEvent::create($data), 201);
    }

    public function destroyEvent(LocalEvent $event) { $event->delete(); return response()->noContent(); }
}
