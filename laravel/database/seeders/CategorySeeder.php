<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $smallBusiness = Category::query()->updateOrCreate(
            ['slug' => 'small-business'],
            ['name' => 'Small Business', 'name_tr' => 'Küçük İşletme', 'name_fa' => 'کسب‌وکار کوچک', 'icon' => 'store', 'sort_order' => 0, 'is_active' => true]
        );

        $children = [
            ['name' => 'Taxi', 'name_tr' => 'Taksi', 'name_fa' => 'تاکسی', 'slug' => 'taxi', 'icon' => 'car', 'content_type' => 'none'],
            ['name' => 'Barber', 'name_tr' => 'Berber', 'name_fa' => 'آرایشگاه مردانه', 'slug' => 'barber', 'icon' => 'scissors', 'content_type' => 'services'],
            ['name' => 'Market', 'name_tr' => 'Market', 'name_fa' => 'سوپرمارکت', 'slug' => 'market', 'icon' => 'shopping-cart', 'content_type' => 'products'],
            ['name' => 'Coffee Shop', 'name_tr' => 'Kahveci', 'name_fa' => 'کافه', 'slug' => 'coffee-shop', 'icon' => 'coffee', 'content_type' => 'none'],
            ['name' => 'Restaurant', 'name_tr' => 'Restoran', 'name_fa' => 'رستوران', 'slug' => 'restaurant', 'icon' => 'utensils-crossed', 'content_type' => 'none'],
        ];

        foreach ($children as $index => $child) {
            Category::query()->updateOrCreate(
                ['slug' => $child['slug']],
                [
                    'parent_id' => $smallBusiness->id,
                    'name' => $child['name'],
                    'name_tr' => $child['name_tr'],
                    'name_fa' => $child['name_fa'],
                    'icon' => $child['icon'],
                    'content_type' => $child['content_type'],
                    'sort_order' => $index,
                    'is_active' => true,
                ]
            );
        }

        $topLevel = [
            ['name' => 'Real Estate', 'name_tr' => 'Emlak', 'name_fa' => 'املاک', 'slug' => 'real-estate', 'icon' => 'building-2', 'content_type' => 'projects'],
            ['name' => 'Construction', 'name_tr' => 'İnşaat', 'name_fa' => 'ساخت‌وساز', 'slug' => 'construction', 'icon' => 'hard-hat', 'content_type' => 'projects'],
            ['name' => 'Hotel & Casino', 'name_tr' => 'Otel & Kumarhane', 'name_fa' => 'هتل و کازینو', 'slug' => 'hotel-casino', 'icon' => 'dice-5', 'content_type' => 'services'],
            ['name' => 'Hair & Beauty', 'name_tr' => 'Kuaför & Güzellik', 'name_fa' => 'آرایش و زیبایی', 'slug' => 'hair-beauty', 'icon' => 'scissors', 'content_type' => 'services'],
            ['name' => 'Retail', 'name_tr' => 'Perakende', 'name_fa' => 'خرده‌فروشی', 'slug' => 'retail', 'icon' => 'shopping-bag', 'content_type' => 'products'],
            ['name' => 'Other', 'name_tr' => 'Diğer', 'name_fa' => 'سایر', 'slug' => 'other', 'icon' => 'more-horizontal', 'content_type' => 'none'],
        ];

        foreach ($topLevel as $index => $category) {
            Category::query()->updateOrCreate(
                ['slug' => $category['slug']],
                [
                    'parent_id' => null,
                    'name' => $category['name'],
                    'name_tr' => $category['name_tr'],
                    'name_fa' => $category['name_fa'],
                    'icon' => $category['icon'],
                    'content_type' => $category['content_type'],
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ]
            );
        }
    }
}
