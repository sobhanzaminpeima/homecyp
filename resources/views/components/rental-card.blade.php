<div class="group bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 border border-gray-100">
    <a href="{{ route('rentals.daily.show', $rental->slug) }}" class="block">
        <div class="relative overflow-hidden h-52">
            @if($rental->cover_image)
                <img src="{{ $rental->cover_image }}" alt="{{ $rental->title }}"
                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
            @else
                <div class="w-full h-full bg-gradient-to-br from-orange-100 to-amber-100 flex items-center justify-center">
                    <span class="text-5xl">🏖️</span>
                </div>
            @endif

            @if($rental->is_airbnb)
            <div class="absolute top-3 right-3 bg-white rounded-lg px-2 py-1 flex items-center space-x-1 shadow">
                <svg class="w-3 h-3 text-[#FF5A5F]" fill="currentColor" viewBox="0 0 32 32">
                    <path d="M16 1C7.716 1 1 7.716 1 16s6.716 15 15 15 15-6.716 15-15S24.284 1 16 1z"/>
                </svg>
                <span class="text-xs font-bold text-[#FF5A5F]">Airbnb</span>
            </div>
            @endif

            @if($rental->price)
            <div class="absolute bottom-3 left-3 bg-white/90 backdrop-blur rounded-lg px-3 py-1.5">
                <span class="font-bold text-gold text-sm">{{ $rental->currency }} {{ number_format($rental->price) }}</span>
                <span class="text-gray-500 text-xs">/ {{ __('night') }}</span>
            </div>
            @endif
        </div>
    </a>

    <div class="p-5">
        <a href="{{ route('rentals.daily.show', $rental->slug) }}">
            <h3 class="font-heading font-bold text-base text-gray-900 mb-1 line-clamp-1 group-hover:text-gold transition-colors">
                {{ $rental->title }}
            </h3>
        </a>

        @if($rental->location)
        <p class="text-xs text-gray-500 flex items-center mb-3">
            <svg class="w-3.5 h-3.5 mr-1 text-gold" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
            </svg>
            {{ $rental->location }}
        </p>
        @endif

        <div class="flex items-center space-x-3 text-xs text-gray-500 mb-4">
            @if($rental->bedrooms)
            <span>🛏 {{ $rental->bedrooms }} {{ __('bed') }}</span>
            @endif
            @if($rental->bathrooms)
            <span>🚿 {{ $rental->bathrooms }} {{ __('bath') }}</span>
            @endif
            @if($rental->has_pool)
            <span>🏊 {{ __('Pool') }}</span>
            @endif
        </div>

        @if($rental->is_airbnb && $rental->airbnb_url)
        <a href="{{ $rental->airbnb_url }}" target="_blank" rel="noopener nofollow"
           class="flex items-center justify-center space-x-2 w-full bg-[#FF5A5F] hover:bg-[#e54b50] text-white py-2.5 rounded-xl text-sm font-semibold transition-colors">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 32 32"><path d="M16 1C7.716 1 1 7.716 1 16s6.716 15 15 15 15-6.716 15-15S24.284 1 16 1z"/></svg>
            <span>{{ __('Book on Airbnb') }}</span>
        </a>
        @else
        <a href="{{ route('rentals.daily.show', $rental->slug) }}"
           class="block text-center btn-gold py-2.5 rounded-xl text-sm font-semibold">
            {{ __('View Details') }}
        </a>
        @endif
    </div>
</div>
