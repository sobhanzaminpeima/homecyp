<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Review;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        return response()->json([
            'businesses_total' => Business::query()->count(),
            'businesses_pending' => Business::query()->where('status', 'pending')->count(),
            'businesses_approved' => Business::query()->where('status', 'approved')->count(),
            'users_total' => User::query()->where('account_type', 'user')->count(),
            'business_owners_total' => User::query()->where('account_type', 'business')->count(),
            'reviews_total' => Review::query()->count(),
            'views_total' => (int) Business::query()->sum('view_count'),
        ]);
    }
}
