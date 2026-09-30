@extends('layouts.app')
@section('title', $seo['title'] ?? $project->title)
@section('meta_description', $seo['description'] ?? '')
@section('og_image', $project->cover_image ?? asset('images/og-default.jpg'))

@push('scripts')
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "Residence",
    "name": @json($project->title),
    "description": @json(strip_tags($project->translation('en')?->description ?? '')),
    "image": @json($project->cover_image),
    "address": { "@@type": "PostalAddress", "addressLocality": @json($project->location), "addressCountry": "CY" }
}
</script>
@endpush

@section('content')
@php
    $gallery = $project->getMedia('gallery');
    $images = $gallery->count() ? $gallery->map(fn($m) => ['full' => $m->getUrl(), 'thumb' => $m->getUrl('thumb')])->values()->all() : [];
    if (empty($images) && $project->cover_image) { $images = [['full' => $project->cover_image, 'thumb' => $project->cover_image]]; }
    $faqs = $project->translation()?->faq ?? [];
    $benefits = $project->investment_benefits ?? [];
@endphp

<div x-data="{
        active: 0,
        total: {{ count($images) }},
        lightbox: false,
        next() { this.active = (this.active + 1) % this.total },
        prev() { this.active = (this.active - 1 + this.total) % this.total }
     }"
     @keydown.window.arrow-right="next()" @keydown.window.arrow-left="prev()" @keydown.window.escape="lightbox=false">

    {{-- ===== HERO SLIDER ===== --}}
    <section class="relative bg-gray-950 pt-20">
        @if(count($images))
        <div class="relative h-[60vh] md:h-[72vh] overflow-hidden">
            @foreach($images as $idx => $img)
            <div x-show="active === {{ $idx }}" x-transition.opacity.duration.600ms class="absolute inset-0">
                <img src="{{ $img['full'] }}" alt="{{ $project->title }} - {{ $idx + 1 }}" class="w-full h-full object-cover">
                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-black/30"></div>
            </div>
            @endforeach

            @if(count($images) > 1)
            <button @click="prev()" class="absolute left-4 top-1/2 -translate-y-1/2 z-20 w-12 h-12 rounded-full bg-white/15 backdrop-blur-md hover:bg-white/30 flex items-center justify-center text-white transition-all">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </button>
            <button @click="next()" class="absolute right-4 top-1/2 -translate-y-1/2 z-20 w-12 h-12 rounded-full bg-white/15 backdrop-blur-md hover:bg-white/30 flex items-center justify-center text-white transition-all">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </button>
            <button @click="lightbox = true" class="absolute top-4 right-4 z-20 px-4 py-2 rounded-lg bg-black/40 backdrop-blur-md text-white text-sm font-medium hover:bg-black/60 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
                <span x-text="(active+1) + ' / {{ count($images) }}'"></span>
            </button>
            @endif

            <div class="absolute bottom-0 left-0 right-0 z-10 p-6 md:p-10">
                <div class="max-w-7xl mx-auto">
                    <div class="flex flex-wrap items-center gap-2 mb-3">
                        <span class="bg-gold text-white text-xs font-semibold px-3 py-1 rounded-full uppercase tracking-wide">{{ __('New Project') }}</span>
                        @if($project->status === 'upcoming')<span class="bg-blue-500 text-white text-xs font-semibold px-3 py-1 rounded-full">{{ __('Upcoming') }}</span>@endif
                        @if($project->developer)<span class="bg-white/15 backdrop-blur text-white text-xs font-semibold px-3 py-1 rounded-full">{{ $project->developer }}</span>@endif
                    </div>
                    <h1 class="font-heading text-3xl md:text-5xl font-bold text-white mb-2">{{ $project->title }}</h1>
                    @if($project->location)
                    <p class="text-white/80 flex items-center gap-2">
                        <x-hicon name="location" class="w-5 h-5 text-gold"/> {{ $project->location }}
                    </p>
                    @endif
                </div>
            </div>
        </div>

        @if(count($images) > 1)
        <div class="max-w-7xl mx-auto px-4 py-4 flex gap-3 overflow-x-auto">
            @foreach($images as $idx => $img)
            <button @click="active = {{ $idx }}" class="shrink-0 w-24 h-16 rounded-lg overflow-hidden ring-2 transition-all"
                    :class="active === {{ $idx }} ? 'ring-gold' : 'ring-transparent opacity-60 hover:opacity-100'">
                <img src="{{ $img['thumb'] }}" alt="" class="w-full h-full object-cover">
            </button>
            @endforeach
        </div>
        @endif
        @endif
    </section>

    {{-- ===== BODY ===== --}}
    <section class="py-12 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-3 gap-10">
            <div class="lg:col-span-2 space-y-10">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    @php
                        $facts = [];
                        if($project->price_from) $facts[] = ['icon'=>'cash','label'=>__('From'),'value'=>$project->currency.' '.number_format($project->price_from)];
                        if($project->completion_date) $facts[] = ['icon'=>'calendar','label'=>__('Completion'),'value'=>$project->completion_date->format('M Y')];
                        $facts[] = ['icon'=>'building','label'=>__('Type'),'value'=>__('Residential')];
                        $facts[] = ['icon'=>'location','label'=>__('Area'),'value'=>$project->region ?: 'North Cyprus'];
                    @endphp
                    @foreach($facts as $f)
                    <div class="bg-gray-50 rounded-2xl p-4 text-center card-lift">
                        <x-hicon :name="$f['icon']" class="w-7 h-7 text-gold mx-auto mb-2"/>
                        <div class="text-xs text-gray-400">{{ $f['label'] }}</div>
                        <div class="font-semibold text-gray-900 text-sm mt-0.5">{{ $f['value'] }}</div>
                    </div>
                    @endforeach
                </div>

                @if($project->translation()?->description)
                <div>
                    <h2 class="font-heading text-2xl font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <x-hicon name="document" class="w-6 h-6 text-gold"/> {{ __('About This Project') }}
                    </h2>
                    <div class="prose max-w-none text-gray-600 leading-relaxed">{!! $project->translation()->description !!}</div>
                </div>
                @endif

                @if($project->amenities)
                <div>
                    <h2 class="font-heading text-2xl font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <x-hicon name="sparkles" class="w-6 h-6 text-gold"/> {{ __('Amenities & Features') }}
                    </h2>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                        @foreach($project->amenities as $a)
                        <div class="flex items-center gap-2 bg-gray-50 rounded-xl px-4 py-3 text-sm text-gray-700">
                            <x-hicon name="check" class="w-5 h-5 text-gold shrink-0"/> {{ $a }}
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                @if(count($benefits))
                <div class="bg-gray-950 rounded-2xl p-8 text-white">
                    <h2 class="font-heading text-2xl font-bold mb-5 flex items-center gap-2">
                        <x-hicon name="chart" class="w-6 h-6 text-gold"/> {{ __('Investment Benefits') }}
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        @foreach($benefits as $b)
                        <div class="flex items-start gap-3">
                            <x-hicon name="check" class="w-5 h-5 text-gold shrink-0 mt-0.5"/>
                            <span class="text-gray-300 text-sm">{{ $b }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                @if($project->properties->where('status','active')->count())
                <div>
                    <h2 class="font-heading text-2xl font-bold text-gray-900 mb-5 flex items-center gap-2">
                        <x-hicon name="home" class="w-6 h-6 text-gold"/> {{ __('Available Units') }}
                    </h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        @foreach($project->properties->where('status','active') as $property)
                            @include('components.property-card', ['property' => $property])
                        @endforeach
                    </div>
                </div>
                @endif

                @if(count($faqs))
                <div x-data="{ open: 0 }">
                    <h2 class="font-heading text-2xl font-bold text-gray-900 mb-5">{{ __('Frequently Asked Questions') }}</h2>
                    <div class="space-y-3">
                        @foreach($faqs as $i => $faq)
                        <div class="border border-gray-200 rounded-xl overflow-hidden">
                            <button @click="open = open === {{ $i }} ? null : {{ $i }}" class="w-full flex items-center justify-between px-5 py-4 text-left font-semibold text-gray-900 hover:bg-gray-50">
                                <span>{{ $faq['question'] ?? '' }}</span>
                                <svg class="w-5 h-5 text-gold shrink-0 transition-transform" :class="open === {{ $i }} ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                            <div x-show="open === {{ $i }}" x-collapse><p class="px-5 pb-4 text-gray-600 text-sm leading-relaxed">{{ $faq['answer'] ?? '' }}</p></div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            <div class="lg:col-span-1">
                <div class="sticky top-28 space-y-4">
                    <div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-xl">
                        @if($project->price_from)
                        <p class="text-sm text-gray-500">{{ __('Starting From') }}</p>
                        <p class="text-3xl font-bold text-gold mb-1">{{ $project->currency }} {{ number_format($project->price_from) }}</p>
                        <p class="text-xs text-gray-400 mb-5">{{ __('Flexible payment plans available') }}</p>
                        @endif

                        <a href="https://wa.me/905338456497?text={{ urlencode(__('Hi, I am interested in: ') . $project->title) }}" target="_blank"
                           class="flex items-center justify-center gap-2 w-full bg-green-500 hover:bg-green-600 text-white py-3 rounded-xl font-semibold mb-3 transition-colors">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51l-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487 3.181 1.371 3.181.914 3.752.857.571-.057 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/></svg>
                            {{ __('Enquire on WhatsApp') }}
                        </a>

                        <form action="{{ route('leads.store') }}" method="POST" class="space-y-3"
                              x-data="{ loading:false, done:false }"
                              @submit.prevent="loading=true; fetch('{{ route('leads.store') }}',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content,'Accept':'application/json'},body:JSON.stringify(Object.fromEntries(new FormData($el)))}).then(r=>r.json()).then(d=>{loading=false;done=true})">
                            @csrf
                            <input type="hidden" name="type" value="investment">
                            <input type="hidden" name="project_id" value="{{ $project->id }}">
                            <input type="hidden" name="source" value="website">
                            <template x-if="!done">
                                <div class="space-y-3">
                                    <p class="text-sm font-semibold text-gray-900">{{ __('Request Full Details & Price List') }}</p>
                                    <input type="text" name="name" placeholder="{{ __('Full Name') }}" required class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:border-gold outline-none">
                                    <input type="tel" name="phone" placeholder="{{ __('Phone / WhatsApp') }}" required class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:border-gold outline-none">
                                    <input type="email" name="email" placeholder="{{ __('Email') }}" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:border-gold outline-none">
                                    <button type="submit" :disabled="loading" class="btn-gold w-full py-3 rounded-xl font-semibold">
                                        <span x-show="!loading">{{ __('Get Price List') }}</span>
                                        <span x-show="loading">{{ __('Sending...') }}</span>
                                    </button>
                                    <p class="text-[11px] text-gray-400 text-center">{{ __('We respect your privacy. No spam.') }}</p>
                                </div>
                            </template>
                            <template x-if="done">
                                <div class="text-center py-6">
                                    <x-hicon name="check" class="w-12 h-12 text-green-500 mx-auto mb-3"/>
                                    <p class="font-semibold text-gray-900">{{ __('Thank You!') }}</p>
                                    <p class="text-sm text-gray-500">{{ __('Our team will contact you shortly.') }}</p>
                                </div>
                            </template>
                        </form>
                    </div>

                    <div class="bg-gray-50 rounded-2xl p-5 text-center">
                        <p class="text-sm text-gray-500 mb-2">{{ __('Speak to an expert') }}</p>
                        <a href="tel:+905338456497" class="font-heading text-xl font-bold text-gray-900 hover:text-gold">+90 533 845 64 97</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ===== LIGHTBOX ===== --}}
    <div x-show="lightbox" x-cloak class="fixed inset-0 z-[100] bg-black/95 flex items-center justify-center" style="display:none">
        <button @click="lightbox=false" class="absolute top-5 right-5 text-white/70 hover:text-white"><svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
        <button @click="prev()" class="absolute left-5 text-white/70 hover:text-white"><svg class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg></button>
        <button @click="next()" class="absolute right-5 text-white/70 hover:text-white"><svg class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg></button>
        @foreach($images as $idx => $img)
        <img x-show="active === {{ $idx }}" src="{{ $img['full'] }}" alt="" class="max-h-[90vh] max-w-[90vw] object-contain">
        @endforeach
    </div>
</div>
@endsection
