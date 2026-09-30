@extends('layouts.app')
@section('title', __('Investment Guide'))
@section('content')
<section class="pt-28 pb-12 bg-gray-950 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="font-heading text-4xl font-bold mb-2">{{ __('Investment Guide') }}</h1>
        <p class="text-gray-400">{{ __('Your complete guide to investing in North Cyprus real estate') }}</p>
    </div>
</section>
<section class="py-16 bg-white">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="space-y-8">
            @foreach([
                ['step'=>'1','title'=>__('Why North Cyprus?'),'desc'=>__('North Cyprus offers some of the highest rental yields in the Mediterranean (8-12%), affordable prices, and a growing tourism market with 300+ sunny days per year.')],
                ['step'=>'2','title'=>__('The Buying Process'),'desc'=>__('Foreigners can purchase property with a valid Title Deed (Koçan). The process involves selecting a property, signing a contract, paying a deposit, and obtaining purchase permission.')],
                ['step'=>'3','title'=>__('Costs & Taxes'),'desc'=>__('Property transfer tax is approximately 6%, VAT is 5%, and stamp duty is 0.5%. These are significantly lower than most European destinations.')],
                ['step'=>'4','title'=>__('Rental Income'),'desc'=>__('Daily rentals near tourist areas can generate excellent returns. Long-term rentals provide stable monthly income with high occupancy rates.')],
                ['step'=>'5','title'=>__('Payment Plans'),'desc'=>__('Many new projects offer flexible payment plans with installments spread over 1-5 years, making investment accessible.')],
            ] as $item)
            <div class="flex gap-5">
                <div class="w-12 h-12 bg-gold text-white rounded-full flex items-center justify-center font-bold text-lg shrink-0">{{ $item['step'] }}</div>
                <div>
                    <h3 class="font-heading font-bold text-xl text-gray-900 mb-2">{{ $item['title'] }}</h3>
                    <p class="text-gray-600 leading-relaxed">{{ $item['desc'] }}</p>
                </div>
            </div>
            @endforeach
        </div>
        <div class="mt-12 bg-gray-950 rounded-2xl p-8 text-center text-white">
            <h3 class="font-heading text-2xl font-bold mb-3">{{ __('Ready to Invest?') }}</h3>
            <p class="text-gray-400 mb-6">{{ __('Speak with our investment experts today.') }}</p>
            <a href="{{ route('contact') }}" class="btn-gold px-8 py-3 rounded-xl font-semibold inline-block">{{ __('Get Free Consultation') }}</a>
        </div>
    </div>
</section>
@endsection
