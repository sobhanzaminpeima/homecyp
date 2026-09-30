@extends('layouts.app')
@section('title', __('Contact Us'))
@section('content')
<section class="pt-28 pb-12 bg-gray-950 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="font-heading text-4xl font-bold mb-2">{{ __('Contact Us') }}</h1>
        <p class="text-gray-400">{{ __('Get in touch with our expert team') }}</p>
    </div>
</section>
<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-2 gap-12">
        <div>
            <h2 class="font-heading text-2xl font-bold text-gray-900 mb-6">{{ __('Get In Touch') }}</h2>
            <div class="space-y-5 mb-8">
                <div class="flex items-start space-x-4">
                    <div class="w-12 h-12 bg-gold/10 rounded-xl flex items-center justify-center text-gold text-xl shrink-0">📞</div>
                    <div><p class="font-semibold text-gray-900">{{ __('Phone') }}</p><a href="tel:+905338456497" class="text-gray-600 hover:text-gold">+90 533 845 64 97</a></div>
                </div>
                <div class="flex items-start space-x-4">
                    <div class="w-12 h-12 bg-gold/10 rounded-xl flex items-center justify-center text-gold text-xl shrink-0">💬</div>
                    <div><p class="font-semibold text-gray-900">WhatsApp</p><a href="https://wa.me/905338456497" target="_blank" class="text-gray-600 hover:text-gold">+90 533 845 64 97</a></div>
                </div>
                <div class="flex items-start space-x-4">
                    <div class="w-12 h-12 bg-gold/10 rounded-xl flex items-center justify-center text-gold text-xl shrink-0">✉️</div>
                    <div><p class="font-semibold text-gray-900">{{ __('Email') }}</p><a href="mailto:info@homecyp.com" class="text-gray-600 hover:text-gold">info@homecyp.com</a></div>
                </div>
                <div class="flex items-start space-x-4">
                    <div class="w-12 h-12 bg-gold/10 rounded-xl flex items-center justify-center text-gold text-xl shrink-0">📍</div>
                    <div><p class="font-semibold text-gray-900">{{ __('Office') }}</p><p class="text-gray-600">North Cyprus, TRNC</p></div>
                </div>
            </div>
        </div>
        <div class="bg-gray-50 rounded-2xl p-8">
            <h2 class="font-heading text-2xl font-bold text-gray-900 mb-6">{{ __('Send Us a Message') }}</h2>
            @if(session('success'))<div class="bg-green-50 border border-green-200 text-green-700 rounded-xl px-4 py-3 mb-4 text-sm">{{ session('success') }}</div>@endif
            <form action="{{ route('contact.send') }}" method="POST" class="space-y-4">
                @csrf
                <input type="text" name="name" placeholder="{{ __('Full Name') }} *" required class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:border-gold outline-none">
                <div class="grid grid-cols-2 gap-4">
                    <input type="tel" name="phone" placeholder="{{ __('Phone') }} *" required class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:border-gold outline-none">
                    <input type="email" name="email" placeholder="{{ __('Email') }}" class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:border-gold outline-none">
                </div>
                <textarea name="message" rows="5" placeholder="{{ __('Your message...') }}" class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:border-gold outline-none resize-none"></textarea>
                <button type="submit" class="btn-gold w-full py-3 rounded-xl font-semibold">{{ __('Send Message') }}</button>
            </form>
        </div>
    </div>
</section>
@endsection
