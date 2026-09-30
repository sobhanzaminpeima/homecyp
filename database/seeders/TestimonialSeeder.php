<?php

namespace Database\Seeders;

use App\Models\Testimonial;
use Illuminate\Database\Seeder;

class TestimonialSeeder extends Seeder
{
    public function run(): void
    {
        $testimonials = [
            ['name' => 'James Mitchell', 'role' => 'Property Investor', 'nationality' => '🇬🇧 UK', 'content' => 'HomeCyp helped me find my dream investment property in Kyrenia. The process was seamless and the ROI has been exceptional. I\'ve already referred 3 friends!', 'rating' => 5],
            ['name' => 'Sarah & Tom Williams', 'role' => 'Holiday Home Buyers', 'nationality' => '🇦🇺 Australia', 'content' => 'We purchased a villa through HomeCyp and couldn\'t be happier. The team was professional, transparent, and guided us through every step of the process.', 'rating' => 5],
            ['name' => 'Mehmet Yılmaz', 'role' => 'Business Owner', 'nationality' => '🇹🇷 Turkey', 'content' => 'Kuzey Kıbrıs\'ta yatırım yapmak için doğru adresi bulduk. Profesyonel ekip ve şeffaf süreç. Kesinlikle tavsiye ederim.', 'rating' => 5],
            ['name' => 'Elena Petrov', 'role' => 'Digital Nomad', 'nationality' => '🇷🇺 Russia', 'content' => 'Rented through Airbnb listings on HomeCyp for 3 months. Beautiful properties, great locations, and the support team was always available. Will definitely return!', 'rating' => 5],
            ['name' => 'Hans Mueller', 'role' => 'Retired Teacher', 'nationality' => '🇩🇪 Germany', 'content' => 'We moved to North Cyprus for retirement and HomeCyp made the property search incredibly easy. Their knowledge of the local market is outstanding.', 'rating' => 5],
            ['name' => 'Ahmed Al-Rashid', 'role' => 'Entrepreneur', 'nationality' => '🇦🇪 UAE', 'content' => 'Excellent service and premium properties. The investment opportunities in North Cyprus are truly outstanding and HomeCyp helped us make the right choice.', 'rating' => 5],
        ];

        foreach ($testimonials as $i => $data) {
            Testimonial::create(array_merge($data, ['is_active' => true, 'sort_order' => $i]));
        }
    }
}
