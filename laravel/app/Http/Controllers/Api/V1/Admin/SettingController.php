<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        return response()->json([
            'views_base' => (int) Setting::get('stats', 'views_base', 0),
            'counter_label' => Setting::get('stats', 'counter_label', 'people explored Easy Cyprus'),
            'site_name' => Setting::get('general', 'site_name', 'Easy Cyprus'),
            'support_email' => Setting::get('general', 'support_email'),
            'contact_phone' => Setting::get('general', 'contact_phone'),
            'ios_app_url' => Setting::get('apps', 'ios_app_url'),
            'android_app_url' => Setting::get('apps', 'android_app_url'),
            'available_locales' => json_decode(Setting::get('general', 'available_locales', '[]'), true),
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'views_base' => ['sometimes', 'integer', 'min:0'],
            'counter_label' => ['sometimes', 'string', 'max:255'],
            'site_name' => ['sometimes', 'string', 'max:255'],
            'support_email' => ['sometimes', 'nullable', 'email'],
            'contact_phone' => ['sometimes', 'nullable', 'string', 'max:50'],
            'ios_app_url' => ['sometimes', 'nullable', 'url', 'max:500'],
            'android_app_url' => ['sometimes', 'nullable', 'url', 'max:500'],
        ]);

        if (array_key_exists('views_base', $data)) {
            Setting::set('stats', 'views_base', $data['views_base']);
        }

        if (array_key_exists('counter_label', $data)) {
            Setting::set('stats', 'counter_label', $data['counter_label']);
        }

        if (array_key_exists('site_name', $data)) {
            Setting::set('general', 'site_name', $data['site_name']);
        }

        if (array_key_exists('support_email', $data)) {
            Setting::set('general', 'support_email', $data['support_email']);
        }

        if (array_key_exists('contact_phone', $data)) {
            Setting::set('general', 'contact_phone', $data['contact_phone']);
        }

        if (array_key_exists('ios_app_url', $data)) {
            Setting::set('apps', 'ios_app_url', $data['ios_app_url']);
        }

        if (array_key_exists('android_app_url', $data)) {
            Setting::set('apps', 'android_app_url', $data['android_app_url']);
        }

        return $this->index();
    }
}
