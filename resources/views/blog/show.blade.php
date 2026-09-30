@extends('layouts.app')
@section('title', $seo['title'] ?? $post->title)
@section('meta_description', $seo['description'] ?? '')
@section('og_image', $post->cover_image ?? asset('images/og-default.jpg'))

@push('scripts')
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "BlogPosting",
    "headline": @json($post->title),
    "image": @json($post->cover_image),
    "datePublished": "{{ optional($post->published_at ?? $post->created_at)->toIso8601String() }}",
    "author": { "@@type": "Organization", "name": "HomeCyp" }
}
</script>
@endpush

@section('content')
<article>
    <section class="relative pt-20 bg-gray-950">
        <div class="relative h-[50vh] overflow-hidden">
            @if($post->cover_image)
            <img src="{{ $post->cover_image }}" alt="{{ $post->title }}" class="w-full h-full object-cover">
            <div class="absolute inset-0 bg-gradient-to-t from-black/85 via-black/40 to-black/30"></div>
            @else
            <div class="w-full h-full bg-gradient-to-br from-gray-800 to-gray-900"></div>
            @endif
            <div class="absolute bottom-0 left-0 right-0 p-6 md:p-10">
                <div class="max-w-3xl mx-auto">
                    <span class="bg-gold text-white text-xs font-semibold px-3 py-1 rounded-full uppercase">{{ $post->category }}</span>
                    <h1 class="font-heading text-3xl md:text-5xl font-bold text-white mt-4 mb-2">{{ $post->title }}</h1>
                    <p class="text-white/70 text-sm">{{ optional($post->published_at ?? $post->created_at)->format('F d, Y') }} · {{ $post->views }} {{ __('views') }}</p>
                </div>
            </div>
        </div>
    </section>

    <section class="py-12 bg-white">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="prose prose-lg max-w-none text-gray-700">{!! $post->translation()?->body !!}</div>

            <div class="mt-10 p-6 bg-gray-950 rounded-2xl text-center text-white">
                <h3 class="font-heading text-xl font-bold mb-2">{{ __('Interested in North Cyprus property?') }}</h3>
                <p class="text-gray-400 text-sm mb-4">{{ __('Our experts are here to help you invest.') }}</p>
                <a href="{{ route('contact') }}" class="btn-gold px-6 py-3 rounded-xl font-semibold inline-block">{{ __('Get Free Consultation') }}</a>
            </div>
        </div>
    </section>

    @if($related->count())
    <section class="py-12 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="font-heading text-2xl font-bold text-gray-900 mb-8">{{ __('Related Articles') }}</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                @foreach($related as $post)
                <a href="{{ route('blog.show', $post->slug) }}" class="group block bg-white rounded-2xl overflow-hidden border border-gray-100 card-lift">
                    <div class="relative h-44 overflow-hidden">
                        @if($post->cover_image)<img src="{{ $post->cover_image }}" alt="{{ $post->title }}" class="w-full h-full object-cover img-zoom">@else<div class="w-full h-full bg-gray-200"></div>@endif
                    </div>
                    <div class="p-5">
                        <h3 class="font-heading font-bold text-base text-gray-900 line-clamp-2 group-hover:text-gold transition-colors">{{ $post->title }}</h3>
                    </div>
                </a>
                @endforeach
            </div>
        </div>
    </section>
    @endif
</article>
@endsection
