@extends('layouts.app')
@section('title', __('Our Agents'))
@section('content')
<section class="pt-28 pb-12 bg-gray-950 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="font-heading text-4xl font-bold mb-2">{{ __('Our Agents') }}</h1>
        <p class="text-gray-400">{{ __('Meet our expert real estate team') }}</p>
    </div>
</section>
<section class="py-16 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        @if($agents->count())
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($agents as $agent)
            <a href="{{ route('agents.show', $agent->id) }}" class="group bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-xl transition-all text-center p-6">
                @if($agent->photo)
                <img src="{{ $agent->photo }}" alt="{{ $agent->name }}" class="w-24 h-24 rounded-full object-cover mx-auto mb-4">
                @else
                <div class="w-24 h-24 rounded-full bg-gold/10 flex items-center justify-center text-gold text-3xl font-bold mx-auto mb-4">{{ substr($agent->name, 0, 1) }}</div>
                @endif
                <h3 class="font-heading font-bold text-lg text-gray-900 group-hover:text-gold">{{ $agent->name }}</h3>
                <p class="text-sm text-gray-500">{{ $agent->title }}</p>
                <p class="text-xs text-gold mt-2">{{ $agent->properties->count() }} {{ __('Properties') }}</p>
            </a>
            @endforeach
        </div>
        @else
        <div class="text-center py-20"><div class="text-6xl mb-4">👥</div><h3 class="text-xl font-semibold text-gray-700">{{ __('No agents listed yet') }}</h3></div>
        @endif
    </div>
</section>
@endsection
