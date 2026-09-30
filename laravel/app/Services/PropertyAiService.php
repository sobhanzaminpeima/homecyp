<?php

namespace App\Services;

use App\Models\AiConversation;
use App\Models\BusinessProject;
use App\Models\City;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;

class PropertyAiService
{
    public function reply(AiConversation $conversation, string $message, string $locale): array
    {
        $filters = $this->extractFilters($message, $conversation->search_context ?? []);
        $properties = $this->findProperties($filters);
        $answer = $this->generate($conversation, $message, $locale, $filters, $properties);

        return ['answer' => $answer, 'properties' => $properties, 'filters' => $filters];
    }

    private function extractFilters(string $message, array $previous): array
    {
        $text = Str::lower($this->normalizeDigits($message));
        $filters = $previous;
        $daily = ['daily','short stay','holiday','vacation','airbnb','روزانه','کوتاه مدت','günlük','tatil','посуточ','краткоср','יומי','يومي','قصيرة الأجل','tagesmiete','ferienwohnung'];
        $rent = ['rent','rental','lease','اجاره','kiralık','kiralama','аренд','השכרה','إيجار','miete','mieten'];
        $sale = ['buy','purchase','sale','خرید','فروش','satılık','satın','купить','продаж','קנייה','מכירה','شراء','بيع','kaufen','verkauf'];
        if ($this->contains($text, $daily)) { $filters['listing_type']='rent'; $filters['rental_period']='daily'; }
        elseif ($this->contains($text, $rent)) { $filters['listing_type']='rent'; unset($filters['rental_period']); }
        elseif ($this->contains($text, $sale)) { $filters['listing_type']='sale'; unset($filters['rental_period']); }

        foreach (['villa'=>['villa','ویلا','вилл','וילה','فيلا'], 'apartment'=>['apartment','flat','آپارتمان','daire','квартир','דירה','شقة','wohnung'], 'land'=>['land','plot','زمین','arsa','земл','קרקע','أرض','grundstück'], 'commercial'=>['commercial','shop','office','تجاری','dükkan','ofis','коммер','משרד','تجاري','gewerbe']] as $type=>$words) {
            if ($this->contains($text,$words)) $filters['property_type']=$type;
        }

        if (preg_match('/(?:bed|bedroom|خوابه|اتاق|yatak|спальн|חדר)[^0-9]{0,8}([1-9])|([1-9])[^0-9]{0,5}(?:bed|bedroom|خوابه|اتاق|yatak|спальн|חדר)/u',$text,$m)) $filters['min_bedrooms']=(int)($m[1] ?: $m[2]);
        if (preg_match('/(?:under|max|budget|up to|زیر|حداکثر|بودجه|تا سقف|altında|bütçe|до|бюджет|أقل من|ميزانية|unter|budget)[^0-9]{0,12}([0-9][0-9,.]*)(?:\s*)(k|thousand|هزار|bin|тысяч|ألف|m|million|میلیون|milyon|миллион|مليون)?/u',$text,$m)) {
            $amount = (float) str_replace(',', '', $m[1]);
            $unit = $m[2] ?? '';
            if (in_array($unit, ['k','thousand','هزار','bin','тысяч','ألف'], true)) $amount *= 1000;
            if (in_array($unit, ['m','million','میلیون','milyon','миллион','مليون'], true)) $amount *= 1000000;
            $filters['max_price'] = $amount;
        }

        foreach (City::where('is_active',true)->get() as $city) {
            $names=array_filter([$city->name,$city->name_tr,$city->name_fa,$city->slug]);
            if ($this->contains($text,array_map(fn($x)=>Str::lower($x),$names))) { $filters['city']=$city->slug; break; }
        }
        return array_intersect_key($filters,array_flip(['listing_type','rental_period','property_type','min_bedrooms','max_price','city']));
    }

    private function findProperties(array $filters): Collection
    {
        $q=BusinessProject::query()->approved()->whereHas('business',fn($x)=>$x->live())->with(['business.city','business.category']);
        foreach (['listing_type','rental_period','property_type'] as $key) if(!empty($filters[$key])) $q->where($key,$filters[$key]);
        if(!empty($filters['min_bedrooms'])) $q->where('bedrooms','>=',$filters['min_bedrooms']);
        if(!empty($filters['max_price'])) $q->where('price','<=',$filters['max_price']);
        if(!empty($filters['city'])) $q->whereHas('business.city',fn($x)=>$x->where('slug',$filters['city']));
        return $q->orderByDesc('is_featured')->latest()->limit(6)->get();
    }

    private function generate(AiConversation $conversation,string $message,string $locale,array $filters,Collection $properties): string
    {
        if (!config('services.openai.key')) return $this->fallback($locale,$properties,$filters);
        $catalog=$properties->map(fn($p)=>['id'=>$p->id,'title'=>$p->title,'type'=>$p->property_type,'listing'=>$p->listing_type,'rental_period'=>$p->rental_period,'price'=>$p->price,'currency'=>$p->currency,'bedrooms'=>$p->bedrooms,'area_m2'=>$p->area_m2,'city'=>$p->business?->city?->name,'address'=>$p->address])->values()->all();
        $history=$conversation->messages()->latest()->limit(10)->get()->reverse()->map(fn($m)=>['role'=>$m->role,'content'=>$m->content])->values()->all();
        $instructions='You are Easy Cyprus AI, a multilingual Northern Cyprus real-estate concierge. Reply in the exact language used by the user. Be warm, concise and practical. Use only the supplied live property catalog for specific recommendations; never invent availability, price, legal facts or property details. Explain that availability and terms require confirmation. Ask at most one useful follow-up question when preferences are missing. Mention property titles naturally but do not output raw JSON. You assist with buying, selling, long-term renting and daily holiday stays. You are not a lawyer or financial adviser.';
        $input=array_merge($history,[['role'=>'user','content'=>$message."\n\nCurrent filters: ".json_encode($filters)."\nLive matching catalog: ".json_encode($catalog,JSON_UNESCAPED_UNICODE)]]);
        try {
            $response=Http::withToken(config('services.openai.key'))->timeout(config('services.openai.timeout'))->post('https://api.openai.com/v1/responses',['model'=>config('services.openai.model'),'instructions'=>$instructions,'input'=>$input,'max_output_tokens'=>700]);
            $response->throw(); $json=$response->json();
            return collect($json['output']??[])->flatMap(fn($o)=>$o['content']??[])->where('type','output_text')->pluck('text')->filter()->join("\n") ?: $this->fallback($locale,$properties,$filters);
        } catch(Throwable $e) { report($e); return $this->fallback($locale,$properties,$filters); }
    }

    private function fallback(string $locale,Collection $properties,array $filters): string
    {
        $count=$properties->count();
        $copy=[
            'fa'=>$count ? "{$count} گزینه واقعی مطابق درخواست شما پیدا کردم. فایل‌ها را پایین ببینید. قیمت و موجودی نهایی را با مشاور تأیید کنید. اگر بودجه، شهر یا تعداد اتاق را بگویید نتایج دقیق‌تر می‌شوند." : 'هنوز فایل دقیقی مطابق این درخواست پیدا نکردم. لطفاً شهر، بودجه، نوع ملک و خرید یا اجاره بودن را بگویید تا جست‌وجو را دقیق‌تر کنم.',
            'tr'=>$count ? "Talebinize uyan {$count} gerçek ilan buldum. Aşağıdaki kartları inceleyin; fiyat ve müsaitliği danışmanla doğrulayın." : 'Henüz tam eşleşme bulamadım. Şehir, bütçe, mülk türü ve satın alma veya kiralama tercihinizi yazın.',
            'ru'=>$count ? "Я нашёл {$count} актуальных вариантов. Посмотрите карточки ниже и уточните цену и доступность у консультанта." : 'Точного совпадения пока нет. Укажите город, бюджет, тип недвижимости и покупку или аренду.',
            'he'=>$count ? "מצאתי {$count} נכסים מתאימים. ניתן לעיין בכרטיסים למטה ולאשר מחיר וזמינות מול היועץ." : 'עדיין לא נמצאה התאמה מדויקת. כתבו עיר, תקציב, סוג נכס והאם מדובר בקנייה או שכירות.',
            'ar'=>$count ? "وجدت {$count} خيارات متاحة تطابق طلبك. راجع البطاقات أدناه وأكد السعر والتوفر مع المستشار." : 'لم أجد تطابقاً دقيقاً بعد. اذكر المدينة والميزانية ونوع العقار وما إذا كنت تريد الشراء أو الإيجار.',
            'de'=>$count ? "Ich habe {$count} passende aktuelle Angebote gefunden. Bitte prüfen Sie Preis und Verfügbarkeit mit dem Anbieter." : 'Ich habe noch keinen genauen Treffer. Nennen Sie Stadt, Budget, Immobilientyp und ob Sie kaufen oder mieten möchten.',
            'en'=>$count ? "I found {$count} live options that match your request. Review the cards below and confirm final price and availability with the agent." : 'I could not find an exact match yet. Tell me your preferred city, budget, property type, and whether you want to buy or rent.',
        ];
        return $copy[$locale]??$copy['en'];
    }

    private function contains(string $text,array $needles):bool { foreach($needles as $n) if($n!==''&&Str::contains($text,Str::lower($n))) return true; return false; }

    private function normalizeDigits(string $value): string
    {
        $value = strtr($value, [
            '۰'=>'0','۱'=>'1','۲'=>'2','۳'=>'3','۴'=>'4','۵'=>'5','۶'=>'6','۷'=>'7','۸'=>'8','۹'=>'9',
            '٠'=>'0','١'=>'1','٢'=>'2','٣'=>'3','٤'=>'4','٥'=>'5','٦'=>'6','٧'=>'7','٨'=>'8','٩'=>'9',
        ]);

        return preg_replace(
            ['/\bیک\b/u','/\bدو\b/u','/\bسه\b/u','/\bچهار\b/u','/\bپنج\b/u'],
            ['1','2','3','4','5'],
            $value,
        ) ?? $value;
    }
}
