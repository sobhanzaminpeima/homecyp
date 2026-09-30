@extends('layouts.app')
@section('title', ($page?->translation()?->title) ?? __('About Us'))
@section('content')
<section class="pt-28 pb-12 bg-gray-950 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="font-heading text-4xl font-bold mb-2">{{ $page?->translation()?->title ?? __('About HomeCyp') }}</h1>
        <p class="text-gray-400">{{ __('Your trusted partner in North Cyprus real estate') }}</p>
    </div>
</section>
<section class="py-16 bg-white">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 prose prose-lg">
        @if($page?->translation()?->content)
            <div class="text-gray-600 leading-relaxed">{!! $page->translation()->content !!}</div>
        @else
        <p class="text-gray-600 leading-relaxed text-lg">{{ __('HomeCyp is a premium real estate platform specializing in luxury properties, investment opportunities, and daily rentals across North Cyprus. With years of experience in the local market, we help international and local clients find their perfect property.') }}</p>
        @endif
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-12 not-prose">
            @foreach([
                ['icon'=>'star','title'=>__('Our Mission'),'desc'=>__('To make North Cyprus property investment simple, transparent, and rewarding for everyone.')],
                ['icon'=>'sparkles','title'=>__('Our Values'),'desc'=>__('Integrity, transparency, and exceptional service in every transaction we handle.')],
                ['icon'=>'shield','title'=>__('Our Promise'),'desc'=>__('Dedicated support from property search through to purchase and beyond.')],
            ] as $item)
            <div class="bg-gray-50 rounded-2xl p-6 text-center card-lift">
                <div class="w-14 h-14 rounded-2xl bg-gold/10 flex items-center justify-center mx-auto mb-3">
                    <x-hicon :name="$item['icon']" class="w-7 h-7 text-gold"/>
                </div>
                <h3 class="font-heading font-bold text-lg mb-2">{{ $item['title'] }}</h3>
                <p class="text-sm text-gray-600">{{ $item['desc'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endsection
