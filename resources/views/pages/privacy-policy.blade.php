@extends('layouts.app')
@section('title', __('Privacy Policy'))
@section('content')
<section class="pt-28 pb-12 bg-gray-950 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="font-heading text-4xl font-bold">{{ __('Privacy Policy') }}</h1>
    </div>
</section>
<section class="py-16 bg-white">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 prose">
        <p class="text-gray-600">{{ __('HomeCyp respects your privacy and is committed to protecting your personal data. This privacy policy explains how we collect, use, and safeguard your information when you use our website.') }}</p>
        <h3 class="font-heading font-bold text-xl mt-6 mb-2">{{ __('Information We Collect') }}</h3>
        <p class="text-gray-600">{{ __('We collect information you provide directly, such as your name, email, phone number, and property preferences when you submit inquiries or contact forms.') }}</p>
        <h3 class="font-heading font-bold text-xl mt-6 mb-2">{{ __('How We Use Your Information') }}</h3>
        <p class="text-gray-600">{{ __('We use your information to respond to inquiries, provide property recommendations, and communicate about our services. We do not sell your data to third parties.') }}</p>
        <h3 class="font-heading font-bold text-xl mt-6 mb-2">{{ __('Contact') }}</h3>
        <p class="text-gray-600">{{ __('For any privacy-related questions, contact us at') }} info@homecyp.com.</p>
    </div>
</section>
@endsection
