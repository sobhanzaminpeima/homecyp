@extends('layouts.app')
@section('title', __('Daily Rentals'))
@section('content')
<section class="pt-28 pb-12 bg-gray-950 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="font-heading text-4xl font-bold mb-2">{{ __('Daily Rentals') }}</h1>
        <p class="text-gray-400">{{ __('Book your perfect holiday home in North Cyprus') }}</p>
    </div>
</section>
<section class="py-12 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        @if($rentals->count())
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($rentals as $rental)
                @include('components.rental-card', ['rental' => $rental])
            @endforeach
        </div>
        <div class="mt-10">{{ $rentals->links() }}</div>
        @else
        <div class="text-center py-20"><div class="text-6xl mb-4">🏖️</div><h3 class="text-xl font-semibold text-gray-700">{{ __('No rentals available yet') }}</h3></div>
        @endif
    </div>
</section>
@endsection
