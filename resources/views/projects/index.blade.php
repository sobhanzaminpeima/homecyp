@extends('layouts.app')
@section('title', __('Projects'))
@section('content')
<section class="pt-28 pb-10 bg-gray-950 text-white relative overflow-hidden">
    <div class="absolute inset-0 bg-[radial-gradient(circle_at_30%_20%,rgba(201,168,76,0.15),transparent_50%)]"></div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
        <h1 class="font-heading text-4xl md:text-5xl font-bold mb-2">{{ __('New Projects') }}</h1>
        <p class="text-gray-400">{{ __('Exclusive new developments across North Cyprus') }}</p>
    </div>
</section>

{{-- ===== PRO SEARCH / FILTER BAR ===== --}}
<section class="bg-white border-b border-gray-200 sticky top-20 z-30 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
        <form action="{{ route('projects.index') }}" method="GET" class="grid grid-cols-2 md:grid-cols-6 gap-3">
            <div class="col-span-2 md:col-span-2 relative">
                <x-hicon name="location" class="w-5 h-5 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2"/>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="{{ __('Search by name or location...') }}"
                       class="w-full pl-10 pr-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:border-gold outline-none">
            </div>
            <select name="region" class="px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:border-gold outline-none">
                <option value="">{{ __('All Regions') }}</option>
                @foreach($regions as $region)
                <option value="{{ $region }}" @selected(request('region')==$region)>{{ $region }}</option>
                @endforeach
            </select>
            <select name="status" class="px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:border-gold outline-none">
                <option value="">{{ __('Any Status') }}</option>
                <option value="active" @selected(request('status')=='active')>{{ __('Available') }}</option>
                <option value="upcoming" @selected(request('status')=='upcoming')>{{ __('Upcoming') }}</option>
                <option value="completed" @selected(request('status')=='completed')>{{ __('Completed') }}</option>
            </select>
            <select name="sort" class="px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:border-gold outline-none">
                <option value="featured" @selected(request('sort')=='featured')>{{ __('Featured First') }}</option>
                <option value="newest" @selected(request('sort')=='newest')>{{ __('Newest') }}</option>
                <option value="price_asc" @selected(request('sort')=='price_asc')>{{ __('Price: Low to High') }}</option>
                <option value="price_desc" @selected(request('sort')=='price_desc')>{{ __('Price: High to Low') }}</option>
            </select>
            <button type="submit" class="btn-gold py-2.5 rounded-lg font-semibold text-sm flex items-center justify-center gap-2">
                <x-hicon name="check" class="w-4 h-4"/> {{ __('Search') }}
            </button>
        </form>
    </div>
</section>

<section class="py-12 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <p class="text-sm text-gray-500 mb-6">{{ $projects->total() }} {{ __('projects found') }}</p>
        @if($projects->count())
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($projects as $project)
            <a href="{{ route('projects.show', $project->slug) }}" class="group block bg-white rounded-2xl overflow-hidden border border-gray-100 card-lift reveal">
                <div class="relative h-56 overflow-hidden">
                    @if($project->cover_image)
                    <img src="{{ $project->cover_image }}" alt="{{ $project->title }}" class="w-full h-full object-cover img-zoom">
                    @else
                    <div class="w-full h-full bg-gradient-to-br from-gray-200 to-gray-300 flex items-center justify-center">
                        <x-hicon name="building" class="w-14 h-14 text-gray-400"/>
                    </div>
                    @endif
                    @if($project->is_featured)<span class="absolute top-3 left-3 bg-gold text-white text-xs font-semibold px-3 py-1 rounded-full">{{ __('Featured') }}</span>@endif
                    <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity flex items-end p-4">
                        <span class="text-white text-sm font-medium flex items-center gap-1">{{ __('View Details') }} <x-hicon name="arrow-right" class="w-4 h-4"/></span>
                    </div>
                </div>
                <div class="p-5">
                    <h3 class="font-heading font-bold text-lg text-gray-900 mb-1 group-hover:text-gold transition-colors">{{ $project->title }}</h3>
                    @if($project->location)<p class="text-sm text-gray-500 mb-3 flex items-center gap-1"><x-hicon name="location" class="w-4 h-4 text-gold"/> {{ $project->location }}</p>@endif
                    @if($project->price_from)<p class="font-semibold text-gold">{{ __('From') }} {{ $project->currency }} {{ number_format($project->price_from) }}</p>@endif
                </div>
            </a>
            @endforeach
        </div>
        <div class="mt-10">{{ $projects->links() }}</div>
        @else
        <div class="text-center py-20">
            <x-hicon name="building" class="w-16 h-16 text-gray-300 mx-auto mb-4"/>
            <h3 class="text-xl font-semibold text-gray-700">{{ __('No projects found') }}</h3>
            <a href="{{ route('projects.index') }}" class="text-gold hover:underline mt-3 inline-block">{{ __('Clear filters') }}</a>
        </div>
        @endif
    </div>
</section>
@endsection
