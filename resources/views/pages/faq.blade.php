@extends('layouts.app')
@section('title', __('FAQ'))
@section('content')
<section class="pt-28 pb-12 bg-gray-950 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="font-heading text-4xl font-bold mb-2">{{ __('Frequently Asked Questions') }}</h1>
        <p class="text-gray-400">{{ __('Everything you need to know about buying in North Cyprus') }}</p>
    </div>
</section>
<section class="py-16 bg-white">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8" x-data="{ active: null }">
        @foreach($faqs as $category => $items)
        <h2 class="font-heading text-2xl font-bold text-gray-900 mb-6 capitalize">{{ $category }}</h2>
        <div class="space-y-4 mb-10">
            @foreach($items as $faq)
            <div class="border border-gray-200 rounded-xl overflow-hidden">
                <button @click="active = active === '{{ $faq->id }}' ? null : '{{ $faq->id }}'" class="w-full flex items-center justify-between px-6 py-4 text-left font-semibold text-gray-900 hover:bg-gray-50">
                    <span>{{ $faq->question }}</span>
                    <svg class="w-5 h-5 text-gold shrink-0 transition-transform" :class="active === '{{ $faq->id }}' ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="active === '{{ $faq->id }}'" x-collapse class="px-6 pb-4 text-gray-600 text-sm leading-relaxed">{{ $faq->answer }}</div>
            </div>
            @endforeach
        </div>
        @endforeach
    </div>
</section>
@endsection
