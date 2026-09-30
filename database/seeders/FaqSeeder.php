<?php

namespace Database\Seeders;

use App\Models\Faq;
use App\Models\FaqTranslation;
use Illuminate\Database\Seeder;

class FaqSeeder extends Seeder
{
    public function run(): void
    {
        $faqs = [
            [
                'en' => ['question' => 'Can foreigners buy property in North Cyprus?', 'answer' => 'Yes, foreigners can purchase property in North Cyprus. The process is straightforward and requires a Title Deed (Koçan) for the property. Non-residents are allowed to buy up to one property (up to 1 donum = 1,338 sqm) under the "Foreigners Permission to Purchase" scheme.'],
                'tr' => ['question' => 'Yabancılar Kuzey Kıbrıs\'ta mülk satın alabilir mi?', 'answer' => 'Evet, yabancılar Kuzey Kıbrıs\'ta mülk satın alabilir. Süreç oldukça basittir ve mülk için Tapu Senedi (Koçan) gerekmektedir.'],
            ],
            [
                'en' => ['question' => 'What is the average property price in North Cyprus?', 'answer' => 'Property prices in North Cyprus vary by location and type. Apartments start from £45,000, villas from £150,000, and luxury properties can exceed £500,000. Prices are significantly lower than comparable Mediterranean destinations.'],
                'tr' => ['question' => 'Kuzey Kıbrıs\'ta ortalama mülk fiyatı nedir?', 'answer' => 'Kuzey Kıbrıs\'ta mülk fiyatları konum ve türe göre değişir. Daireler 45.000 £\'dan, villalar 150.000 £\'dan başlar.'],
            ],
            [
                'en' => ['question' => 'What is the rental yield in North Cyprus?', 'answer' => 'North Cyprus offers excellent rental yields of 8-12% per year, which is one of the highest in the Mediterranean. Daily rental properties near tourist areas can achieve even higher returns during peak season.'],
                'tr' => ['question' => 'Kuzey Kıbrıs\'ta kira getirisi nedir?', 'answer' => 'Kuzey Kıbrıs, yılda %8-12 oranında mükemmel kira getirisi sunmaktadır; bu, Akdeniz\'deki en yüksek oranlardan biridir.'],
            ],
            [
                'en' => ['question' => 'How do I book a daily rental property?', 'answer' => 'You can book daily rental properties directly through our website or via Airbnb. Click the "Book on Airbnb" button on the property page to proceed with your booking, or contact us directly via WhatsApp or phone.'],
                'tr' => ['question' => 'Günlük kiralık mülk nasıl rezerve edilir?', 'answer' => 'Günlük kiralık mülkleri web sitemiz veya Airbnb aracılığıyla rezerve edebilirsiniz. WhatsApp veya telefon ile de bize ulaşabilirsiniz.'],
            ],
            [
                'en' => ['question' => 'What payment methods are accepted?', 'answer' => 'We accept bank transfers, credit/debit cards, and cryptocurrency. Payment plans are available for new project purchases with installments typically spread over 1-5 years.'],
                'tr' => ['question' => 'Hangi ödeme yöntemleri kabul edilmektedir?', 'answer' => 'Banka transferi, kredi/banka kartı ve kripto para kabul ediyoruz. Yeni proje alımları için taksit planları mevcuttur.'],
            ],
        ];

        foreach ($faqs as $faqData) {
            $faq = Faq::create(['category' => 'general', 'is_active' => true, 'sort_order' => 0]);
            FaqTranslation::create(array_merge(['faq_id' => $faq->id, 'locale' => 'en'], $faqData['en']));
            FaqTranslation::create(array_merge(['faq_id' => $faq->id, 'locale' => 'tr'], $faqData['tr']));
        }
    }
}
