<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Setting;

class SettingController extends Controller
{
    /**
     * Public, safe-only subset of settings for the frontend (no contact
     * details or internal counters — those stay admin-only).
     */
    public function index()
    {
        return response()->json([
            'ios_app_url' => Setting::get('apps', 'ios_app_url'),
            'android_app_url' => Setting::get('apps', 'android_app_url'),
        ]);
    }
}
