<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        Setting::set('stats', 'views_base', 1280);
        Setting::set('stats', 'counter_label', 'people explored Easy Cyprus');
        Setting::set('general', 'available_locales', json_encode(['en', 'tr', 'ru', 'fa', 'he']));
        Setting::set('general', 'site_name', 'Easy Cyprus');
        Setting::set('general', 'support_email', 'support@easycyprus.com');
    }
}
