@extends('layouts.app')
@section('title', $agent->name)
@section('content')
<section class="pt-28 pb-12 bg-gray-950 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row items-center gap-6">
        @if($agent->photo)
        <img src="{{ $agent->photo }}" alt="{{ $agent->name }}" class="w-28 h-28 rounded-full object-cover border-4 border-gold">
        @else
        <div class="w-28 h-28 rounded-full bg-gold/20 flex items-center justify-center text-gold text-4xl font-bold">{{ substr($agent->name, 0, 1) }}</div>
        @endif
        <div class="text-center md:text-left">
            <h1 class="font-heading text-3xl font-bold">{{ $agent->name }}</h1>
            <p class="text-gray-400">{{ $agent->title }}</p>
            <div class="flex gap-3 mt-3 justify-center md:justify-start">
                @if($agent->phone)<a href="tel:{{ $agent->phone }}" class="bg-white/10 px-4 py-2 rounded-lg text-sm hover:bg-gold transition-colors">📞 {{ $agent->phone }}</a>@endif
                @if($agent->whatsapp)<a href="https://wa.me/{{ $agent->whatsapp }}" target="_blank" class="bg-green-500 px-4 py-2 rounded-lg text-sm hover:bg-green-600">WhatsApp</a>@endif
            </div>
        </div>
    </div>
</section>
<section class="py-12 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        @if($agent->bio)<p class="text-gray-600 leading-relaxed mb-10 max-w-3xl">{{ $agent->bio }}</p>@endif
        <h2 class="font-heading text-2xl font-bold text-gray-900 mb-6">{{ __('Listings') }}</h2>
        @if($properties->count())
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($properties as $property)
                @include('components.property-card', ['property' => $property])
            @endforeach
        </div>
        <div class="mt-10">{{ $properties->links() }}</div>
        @else
        <p class="text-gray-500">{{ __('No properties listed yet.') }}</p>
        @endif
    </div>
</section>
@endsection
