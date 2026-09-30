@extends('layouts.app')
@section('title', __('Terms & Conditions'))
@section('content')
<section class="pt-28 pb-12 bg-gray-950 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="font-heading text-4xl font-bold">{{ __('Terms & Conditions') }}</h1>
    </div>
</section>
<section class="py-16 bg-white">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 prose">
        <p class="text-gray-600">{{ __('By accessing and using the HomeCyp website, you accept and agree to be bound by these terms and conditions.') }}</p>
        <h3 class="font-heading font-bold text-xl mt-6 mb-2">{{ __('Use of Website') }}</h3>
        <p class="text-gray-600">{{ __('The content on this website is for general information purposes. Property details, prices, and availability are subject to change without notice.') }}</p>
        <h3 class="font-heading font-bold text-xl mt-6 mb-2">{{ __('Property Listings') }}</h3>
        <p class="text-gray-600">{{ __('While we strive for accuracy, all property information should be independently verified. HomeCyp acts as an intermediary and is not liable for inaccuracies provided by developers or sellers.') }}</p>
        <h3 class="font-heading font-bold text-xl mt-6 mb-2">{{ __('Contact') }}</h3>
        <p class="text-gray-600">{{ __('For questions about these terms, contact us at') }} info@homecyp.com.</p>
    </div>
</section>
@endsection
