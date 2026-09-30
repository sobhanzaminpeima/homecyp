@props(['property', 'sponsored' => false, 'sponsorClickUrl' => null])

@php
    $roiEstimate = $property->investment_benefits['rental_yield_percent'] ?? null;
@endphp

<div class="relative rounded-xl overflow-hidden hc-surface border hc-shadow" style="border-radius: var(--hc-radius-card);">
    @if($sponsored)
        <span class="absolute top-2 left-2 z-10 hc-coral-bg text-[10px] font-semibold uppercase tracking-wide px-2 py-1 rounded-full">
            {{ __('Sponsored') }}
        </span>
    @endif

    <a href="{{ $sponsored ? $sponsorClickUrl : route('properties.show', $property->slug) }}" class="block hc-focusable">
        <div class="aspect-[4/3]" style="background: var(--hc-border);">
            @if($property->cover_image)
                <img src="{{ $property->cover_image }}" alt="{{ $property->title }}" class="w-full h-full object-cover">
            @else
                <div class="w-full h-full flex items-center justify-center hc-text-secondary">
                    <x-chat.icon name="image" :size="32" />
                </div>
            @endif
        </div>
        <div class="p-4">
            <p class="font-medium line-clamp-1" style="color: var(--hc-text);">{{ $property->title ?: $property->slug }}</p>

            <div class="flex items-center gap-3 mt-1.5 text-sm hc-text-secondary">
                @if($property->bedrooms)
                    <span class="flex items-center gap-1"><x-chat.icon name="bed" :size="14" /> {{ $property->bedrooms }}</span>
                @endif
                <span class="flex items-center gap-1"><x-chat.icon name="map-pin" :size="14" /> {{ $property->region ?? $property->location }}</span>
                @if($roiEstimate)
                    <span class="flex items-center gap-1" style="color: var(--hc-success);"><x-chat.icon name="trending-up" :size="14" /> {{ $roiEstimate }}%</span>
                @endif
            </div>

            <p class="hc-font-display font-semibold mt-2 text-lg" style="color: var(--hc-primary);">
                {{ $property->currency }} {{ number_format($property->price) }}
            </p>

            @if($property->payment_plan)
                <p class="text-xs hc-text-secondary mt-1">{{ $property->payment_plan }}</p>
            @endif
        </div>
    </a>

    @if(!$sponsored)
        <div class="px-4 pb-4 flex gap-2">
            @if($property->is_airbnb && $property->airbnb_url)
                <a href="{{ $property->airbnb_url }}" target="_blank" rel="noopener"
                   class="hc-focusable flex-1 flex items-center justify-center gap-1.5 text-white text-sm font-medium py-2 rounded-lg"
                   style="background: #FF5A5F;">
                    {{ __('Book on Airbnb') }}
                </a>
            @else
                <a href="{{ route('properties.show', $property->slug) }}#contact"
                   class="hc-focusable flex-1 text-center text-sm font-medium py-2 rounded-lg hc-primary-bg">
                    {{ __('Book Viewing') }}
                </a>
                <a href="https://wa.me/905338456497?text={{ urlencode('Hi, I am interested in: '.$property->title) }}" target="_blank" rel="noopener"
                   class="hc-focusable flex items-center justify-center gap-1.5 text-sm font-medium py-2 px-3 rounded-lg border-2"
                   style="border-color: var(--hc-success); color: var(--hc-success);">
                    {{ __('WhatsApp') }}
                </a>
            @endif
        </div>
    @endif
</div>
