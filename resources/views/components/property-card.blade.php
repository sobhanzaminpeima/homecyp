@php
    $cardUrl = match($property->category) {
        'daily_rental' => route('rentals.daily.show', $property->slug),
        'long_term_rental' => route('rentals.longterm.show', $property->slug),
        'project' => $property->project ? route('projects.show', $property->project->slug) : route('properties.show', $property->slug),
        default => route('properties.show', $property->slug),
    };
@endphp
<a href="{{ $cardUrl }}"
   class="group block bg-white rounded-2xl overflow-hidden border border-gray-100 card-lift reveal">
    <div class="relative overflow-hidden h-48">
        @if($property->cover_image)
            <img src="{{ $property->cover_image }}" alt="{{ $property->title }}"
                 loading="lazy" width="400" height="300"
                 class="w-full h-full object-cover img-zoom">
        @else
            <div class="w-full h-full bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center">
                <svg class="w-12 h-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
                    <polyline points="9 22 9 12 15 12 15 22"/>
                </svg>
            </div>
        @endif

        {{-- Category Badge --}}
        <span class="absolute top-3 left-3 text-xs font-semibold px-2.5 py-1 rounded-full
            {{ $property->category === 'daily_rental' ? 'bg-orange-500 text-white' :
               ($property->category === 'resale' ? 'bg-blue-500 text-white' : 'bg-gold text-white') }}">
            {{ match($property->category) {
                'project' => __('Project'),
                'resale' => __('Resale'),
                'daily_rental' => __('Daily'),
                'long_term_rental' => __('Long-Term'),
                default => $property->category,
            } }}
        </span>

        @if($property->is_airbnb)
        <span class="absolute top-3 right-3 bg-[#FF5A5F] text-white text-xs font-bold px-2 py-1 rounded-full">Airbnb</span>
        @endif
    </div>

    <div class="p-4">
        <h3 class="font-heading font-bold text-base text-gray-900 mb-1 line-clamp-1 group-hover:text-gold transition-colors">
            {{ $property->title }}
        </h3>

        @if($property->location)
        <p class="text-xs text-gray-500 flex items-center mb-3">
            <svg class="w-3.5 h-3.5 mr-1 text-gold" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
            </svg>
            {{ $property->location }}
        </p>
        @endif

        <div class="flex items-center justify-between">
            @if($property->price)
            <p class="font-bold text-gold">
                {{ $property->currency }} {{ number_format($property->price) }}
            </p>
            @endif
            <div class="flex items-center space-x-3 text-gray-500 text-xs">
                @if($property->bedrooms)
                <span class="flex items-center space-x-1">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    <span>{{ $property->bedrooms }}</span>
                </span>
                @endif
                @if($property->bathrooms)
                <span class="flex items-center space-x-1">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <span>{{ $property->bathrooms }}</span>
                </span>
                @endif
                @if($property->area)
                <span>{{ $property->area }}㎡</span>
                @endif
            </div>
        </div>
    </div>
</a>
