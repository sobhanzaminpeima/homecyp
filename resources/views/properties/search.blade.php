@extends('layouts.app')
@section('title', __('Search Results'))
@section('content')
<section class="pt-28 pb-12 bg-gray-950 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="font-heading text-4xl font-bold mb-2">{{ __('Search Results') }}</h1>
        <p class="text-gray-400">{{ $properties->total() }} {{ __('properties found') }}</p>
    </div>
</section>
@include('components.search-filters')
<section class="py-12 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        @if($properties->count())
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($properties as $property)
                @include('components.property-card', ['property' => $property])
            @endforeach
        </div>
        <div class="mt-10">{{ $properties->links() }}</div>
        @else
        <div class="text-center py-20"><div class="text-6xl mb-4">🔍</div><h3 class="text-xl font-semibold text-gray-700">{{ __('No properties match your search') }}</h3><a href="{{ route('properties.index') }}" class="text-gold hover:underline mt-3 inline-block">{{ __('View all properties') }}</a></div>
        @endif
    </div>
</section>
@endsection
