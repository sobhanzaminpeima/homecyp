@extends('layouts.app')
@section('title', __('Blog'))
@section('meta_description', __('Insights, guides and news about North Cyprus real estate and investment.'))
@section('content')
<section class="pt-28 pb-10 bg-gray-950 text-white relative overflow-hidden">
    <div class="absolute inset-0 bg-[radial-gradient(circle_at_70%_20%,rgba(201,168,76,0.15),transparent_50%)]"></div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
        <p class="text-gold text-sm font-semibold uppercase tracking-widest mb-2">{{ __('Insights & Guides') }}</p>
        <h1 class="font-heading text-4xl md:text-5xl font-bold mb-2">{{ __('HomeCyp Blog') }}</h1>
        <p class="text-gray-400">{{ __('News, tips and guides about North Cyprus real estate') }}</p>
    </div>
</section>

<section class="py-12 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        @if($featured)
        <a href="{{ route('blog.show', $featured->slug) }}" class="group grid grid-cols-1 lg:grid-cols-2 gap-0 bg-white rounded-3xl overflow-hidden border border-gray-100 card-lift mb-12">
            <div class="relative h-64 lg:h-auto overflow-hidden">
                @if($featured->cover_image)<img src="{{ $featured->cover_image }}" alt="{{ $featured->title }}" class="w-full h-full object-cover img-zoom">@else<div class="w-full h-full bg-gray-200"></div>@endif
                <span class="absolute top-4 left-4 bg-gold text-white text-xs font-semibold px-3 py-1 rounded-full uppercase">{{ __('Featured') }}</span>
            </div>
            <div class="p-8 flex flex-col justify-center">
                <span class="text-xs text-gold font-semibold uppercase tracking-wide mb-2">{{ $featured->category }}</span>
                <h2 class="font-heading text-2xl md:text-3xl font-bold text-gray-900 mb-3 group-hover:text-gold transition-colors">{{ $featured->title }}</h2>
                <p class="text-gray-600 mb-4 line-clamp-2">{{ $featured->translation()?->excerpt }}</p>
                <span class="text-gold font-semibold text-sm flex items-center gap-1">{{ __('Read More') }} <x-hicon name="arrow-right" class="w-4 h-4"/></span>
            </div>
        </a>
        @endif

        @if($posts->count())
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($posts as $post)
            <a href="{{ route('blog.show', $post->slug) }}" class="group block bg-white rounded-2xl overflow-hidden border border-gray-100 card-lift reveal">
                <div class="relative h-52 overflow-hidden">
                    @if($post->cover_image)<img src="{{ $post->cover_image }}" alt="{{ $post->title }}" class="w-full h-full object-cover img-zoom">@else<div class="w-full h-full bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center"><x-hicon name="document" class="w-12 h-12 text-gray-300"/></div>@endif
                    <span class="absolute top-3 left-3 bg-white/90 backdrop-blur text-gray-700 text-xs font-semibold px-2.5 py-1 rounded-full uppercase">{{ $post->category }}</span>
                </div>
                <div class="p-5">
                    <p class="text-xs text-gray-400 mb-2">{{ optional($post->published_at ?? $post->created_at)->format('M d, Y') }}</p>
                    <h3 class="font-heading font-bold text-lg text-gray-900 mb-2 line-clamp-2 group-hover:text-gold transition-colors">{{ $post->title }}</h3>
                    <p class="text-sm text-gray-500 line-clamp-2">{{ $post->translation()?->excerpt }}</p>
                </div>
            </a>
            @endforeach
        </div>
        <div class="mt-10">{{ $posts->links() }}</div>
        @else
        <div class="text-center py-20">
            <x-hicon name="document" class="w-16 h-16 text-gray-300 mx-auto mb-4"/>
            <h3 class="text-xl font-semibold text-gray-700">{{ __('No posts yet') }}</h3>
            <p class="text-gray-500 mt-2">{{ __('Check back soon for news and guides.') }}</p>
        </div>
        @endif
    </div>
</section>
@endsection
