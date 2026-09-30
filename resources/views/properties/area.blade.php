@extends('layouts.app')

@section('title', __('Property in :area', ['area' => $region]))
@section('meta_description', __('Browse verified properties for sale and rent in :area, North Cyprus. Compare current listings and ask HomeCyp AI for multilingual guidance.', ['area' => $region]))

@section('content')
<section class="max-w-7xl mx-auto px-4 py-12">
    <nav class="text-sm text-gray-500 mb-5"><a href="{{ route('listings') }}">{{ __('Listings') }}</a> / {{ $region }}</nav>
    <h1 class="font-heading text-3xl md:text-5xl font-semibold">{{ __('Property in :area, North Cyprus', ['area' => $region]) }}</h1>
    <p class="mt-4 max-w-3xl text-gray-600">{{ __('Explore current HomeCyp listings in :area. Prices and availability are confirmed with the listing agent before a transaction.', ['area' => $region]) }}</p>
    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6 mt-10">
        @forelse($properties as $property)<x-property-card :property="$property" />@empty
            <p class="col-span-full text-gray-500">{{ __('No active listings are available in this area right now. Ask HomeCyp AI to broaden your search.') }}</p>
        @endforelse
    </div>
    <div class="mt-8">{{ $properties->links() }}</div>
</section>
<script type="application/ld+json">{!! json_encode(['@context'=>'https://schema.org','@type'=>'CollectionPage','name'=>"Property in {$region}, North Cyprus",'url'=>url()->current()], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}</script>
@endsection
