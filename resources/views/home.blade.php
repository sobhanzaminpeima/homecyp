@extends('layouts.app')

@section('title', __('Luxury Real Estate & Daily Rentals in North Cyprus'))
@section('meta_description', __('Premium investment opportunities, luxury residences, and exclusive Airbnb rentals in North Cyprus. Discover villas, apartments, and projects in Kyrenia & Famagusta.'))

@section('content')
@php
    $sec = fn($key) => \App\Models\SiteSetting::get($key, '1') === '1';
    $heroTitle = \App\Models\SiteSetting::get('hero_title');
    $heroSubtitle = \App\Models\SiteSetting::get('hero_subtitle');
@endphp

{{-- ===== HERO SECTION ===== --}}
<section class="relative min-h-screen flex items-center justify-center overflow-hidden">
    {{-- Background with Ken Burns --}}
    @php $heroImg = \App\Models\SiteSetting::get('hero_image_path'); @endphp
    <div class="absolute inset-0 z-0 overflow-hidden">
        <img src="{{ $heroImg ? \Illuminate\Support\Facades\Storage::disk('public')->url($heroImg) : asset('images/hero-bg.jpg') }}" alt="North Cyprus" class="w-full h-full object-cover kenburns" fetchpriority="high">
        <div class="absolute inset-0 bg-gradient-to-b from-black/80 via-black/55 to-gray-950"></div>
        <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_center,transparent_0%,rgba(0,0,0,0.4)_100%)]"></div>
    </div>

    {{-- Decorative floating orbs --}}
    <div class="absolute top-1/4 left-10 w-72 h-72 bg-gold/10 rounded-full blur-3xl floaty"></div>
    <div class="absolute bottom-1/4 right-10 w-96 h-96 bg-blue-500/10 rounded-full blur-3xl floaty" style="animation-delay:-2s"></div>

    {{-- Content --}}
    <div class="relative z-10 text-center text-white px-4 max-w-5xl mx-auto pt-24"
         x-data="{ show: false }" x-init="setTimeout(() => show = true, 100)">
        <div class="inline-flex items-center space-x-2 bg-white/10 backdrop-blur-md border border-white/20 rounded-full px-5 py-2 mb-8 transition-all duration-700"
             :class="show ? 'opacity-100 translate-y-0' : 'opacity-0 -translate-y-4'">
            <span class="w-2 h-2 bg-gold rounded-full animate-pulse"></span>
            <span class="text-sm font-medium tracking-wide">North Cyprus • TRNC</span>
        </div>

        <h1 class="font-heading text-4xl md:text-6xl lg:text-7xl font-bold leading-tight mb-6 transition-all duration-1000 delay-100"
            :class="show ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-6'">
            @if($heroTitle)
            {!! nl2br(e($heroTitle)) !!}
            @else
            {{ __('Luxury Real Estate') }}<br>
            <span class="text-gradient-gold">& {{ __('Daily Rentals') }}</span><br>
            {{ __('in North Cyprus') }}
            @endif
        </h1>

        <p class="text-lg md:text-xl text-white/80 max-w-2xl mx-auto mb-10 leading-relaxed transition-all duration-1000 delay-300"
           :class="show ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-6'">
            {{ $heroSubtitle ?: __('Premium investment opportunities, luxury residences, and exclusive Airbnb rentals in North Cyprus.') }}
        </p>

        <div class="flex flex-col sm:flex-row items-center justify-center gap-4 mb-16 transition-all duration-1000 delay-500"
             :class="show ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-6'">
            <a href="{{ route('properties.index') }}"
               class="btn-gold px-8 py-4 rounded-xl text-base font-semibold flex items-center space-x-2 group">
                <span>{{ __('View Properties') }}</span>
                <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                </svg>
            </a>
            <a href="{{ route('rentals.daily') }}"
               class="bg-white/10 backdrop-blur-md border border-white/30 hover:bg-white/20 text-white px-8 py-4 rounded-xl text-base font-semibold transition-all">
                {{ __('Daily Rentals') }}
            </a>
            <a href="{{ route('contact') }}"
               class="border border-white/40 hover:border-gold hover:text-gold text-white px-8 py-4 rounded-xl text-base font-semibold transition-all">
                {{ __('Contact Us') }}
            </a>
        </div>

        {{-- Stats --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-6 max-w-3xl mx-auto transition-all duration-1000 delay-700"
             :class="show ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-6'">
            @foreach([
                ['number' => $stats['properties'] . '+', 'label' => __('Properties')],
                ['number' => $stats['projects'] . '+', 'label' => __('Projects')],
                ['number' => $stats['happy_clients'], 'label' => __('Happy Clients')],
                ['number' => $stats['years_experience'], 'label' => __('Years Experience')],
            ] as $stat)
            <div class="bg-white/10 backdrop-blur-md rounded-2xl p-4 border border-white/10 hover:border-gold/40 transition-all">
                <div class="font-heading text-3xl font-bold text-gradient-gold">{{ $stat['number'] }}</div>
                <div class="text-sm text-white/70 mt-1">{{ $stat['label'] }}</div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Scroll indicator --}}
    <div class="absolute bottom-8 left-1/2 transform -translate-x-1/2 animate-bounce z-10">
        <svg class="w-6 h-6 text-white/60" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </div>
</section>

{{-- ===== SEARCH BAR ===== --}}
<section class="relative z-20 -mt-8">
    <div class="max-w-5xl mx-auto px-4">
        <div class="bg-white rounded-2xl shadow-2xl p-6">
            <form action="{{ route('properties.search') }}" method="GET" class="grid grid-cols-2 md:grid-cols-5 gap-4">
                <select name="category" class="col-span-1 px-4 py-3 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-gold/30 focus:border-gold outline-none">
                    <option value="">{{ __('All Types') }}</option>
                    <option value="project">{{ __('New Projects') }}</option>
                    <option value="resale">{{ __('Resale') }}</option>
                    <option value="daily_rental">{{ __('Daily Rental') }}</option>
                    <option value="long_term_rental">{{ __('Long-Term') }}</option>
                </select>
                <select name="type" class="col-span-1 px-4 py-3 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-gold/30 focus:border-gold outline-none">
                    <option value="">{{ __('Property Type') }}</option>
                    <option value="apartment">{{ __('Apartment') }}</option>
                    <option value="villa">{{ __('Villa') }}</option>
                    <option value="penthouse">{{ __('Penthouse') }}</option>
                    <option value="studio">{{ __('Studio') }}</option>
                </select>
                <select name="bedrooms" class="col-span-1 px-4 py-3 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-gold/30 focus:border-gold outline-none">
                    <option value="">{{ __('Bedrooms') }}</option>
                    <option value="1">1+</option>
                    <option value="2">2+</option>
                    <option value="3">3+</option>
                    <option value="4">4+</option>
                </select>
                <input type="text" name="location" placeholder="{{ __('Location...') }}"
                       class="col-span-1 px-4 py-3 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-gold/30 focus:border-gold outline-none">
                <button type="submit" class="btn-gold py-3 px-6 rounded-xl font-semibold text-sm flex items-center justify-center space-x-2">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <span>{{ __('Search') }}</span>
                </button>
            </form>
        </div>
    </div>
</section>

{{-- ===== FEATURED PROJECTS ===== --}}
@if($sec('section_projects') && $featuredProjects->count())
<section class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-end justify-between mb-12">
            <div>
                <p class="text-gold text-sm font-semibold uppercase tracking-widest mb-2">{{ __('New Developments') }}</p>
                <h2 class="font-heading text-3xl md:text-4xl font-bold text-gray-900">{{ __('Featured Projects') }}</h2>
            </div>
            <a href="{{ route('projects.index') }}" class="hidden md:flex items-center space-x-2 text-gold font-semibold text-sm hover:underline">
                <span>{{ __('View All') }}</span>
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                </svg>
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($featuredProjects as $project)
            <a href="{{ route('projects.show', $project->slug) }}"
               class="group block bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 border border-gray-100">
                <div class="relative overflow-hidden h-56">
                    @if($project->cover_image)
                        <img src="{{ $project->cover_image }}" alt="{{ $project->title }}"
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                    @else
                        <div class="w-full h-full bg-gradient-to-br from-gray-200 to-gray-300 flex items-center justify-center">
                            <svg class="w-16 h-16 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                        </div>
                    @endif
                    @if($project->is_featured)
                    <span class="absolute top-3 left-3 bg-gold text-white text-xs font-semibold px-3 py-1 rounded-full">
                        {{ __('Featured') }}
                    </span>
                    @endif
                    @if($project->status === 'upcoming')
                    <span class="absolute top-3 right-3 bg-blue-500 text-white text-xs font-semibold px-3 py-1 rounded-full">
                        {{ __('Upcoming') }}
                    </span>
                    @endif
                </div>
                <div class="p-5">
                    <h3 class="font-heading font-bold text-lg text-gray-900 mb-1 group-hover:text-gold transition-colors">
                        {{ $project->title }}
                    </h3>
                    @if($project->location)
                    <p class="text-sm text-gray-500 flex items-center mb-3">
                        <svg class="w-4 h-4 mr-1 text-gold" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        </svg>
                        {{ $project->location }}
                    </p>
                    @endif
                    @if($project->price_from)
                    <p class="font-semibold text-gold text-lg">
                        {{ __('From') }} {{ $project->currency }} {{ number_format($project->price_from) }}
                    </p>
                    @endif
                </div>
            </a>
            @endforeach
        </div>

        <div class="text-center mt-10 md:hidden">
            <a href="{{ route('projects.index') }}" class="btn-gold px-8 py-3 rounded-xl font-semibold">{{ __('View All Projects') }}</a>
        </div>
    </div>
</section>
@endif

{{-- ===== TABBED LISTINGS BY CATEGORY ===== --}}
@php
    $tabs = [
        'project' => ['label' => __('New Projects'), 'icon' => 'building', 'route' => route('projects.index')],
        'resale' => ['label' => __('Resale'), 'icon' => 'key', 'route' => route('resale.index')],
        'daily_rental' => ['label' => __('Daily Rentals'), 'icon' => 'beach', 'route' => route('rentals.daily')],
        'long_term_rental' => ['label' => __('Long-Term'), 'icon' => 'home', 'route' => route('rentals.longterm')],
    ];
    $firstTab = collect($tabbedListings)->filter(fn($c) => $c->count())->keys()->first() ?? 'project';
@endphp
@if($sec('section_tabs'))
<section class="py-20 bg-gray-50 reveal" x-data="{ tab: '{{ $firstTab }}' }">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-10">
            <p class="text-gold text-sm font-semibold uppercase tracking-widest mb-2">{{ __('Explore') }}</p>
            <h2 class="font-heading text-3xl md:text-4xl font-bold text-gray-900">{{ __('Browse by Category') }}</h2>
            <p class="text-gray-500 mt-2">{{ __('Switch between categories to find exactly what you need') }}</p>
        </div>

        {{-- Tab buttons --}}
        <div class="flex flex-wrap justify-center gap-2 md:gap-3 mb-12 border-b border-gray-200">
            @foreach($tabs as $key => $tab)
            <button @click="tab = '{{ $key }}'"
                    class="flex items-center gap-2 px-5 md:px-7 py-3 text-sm md:text-base font-semibold transition-all duration-300"
                    :class="tab === '{{ $key }}' ? 'text-gold tab-active' : 'text-gray-500 hover:text-gray-800'">
                <x-hicon :name="$tab['icon']" class="w-5 h-5"/>
                <span>{{ $tab['label'] }}</span>
                <span class="text-xs bg-gray-200 rounded-full px-2 py-0.5"
                      :class="tab === '{{ $key }}' ? 'bg-gold/15 text-gold' : ''">{{ $tabbedListings[$key]->count() }}</span>
            </button>
            @endforeach
        </div>

        {{-- Tab panels --}}
        @foreach($tabs as $key => $tab)
        <div x-show="tab === '{{ $key }}'" x-cloak
             x-transition:enter="transition ease-out duration-500"
             x-transition:enter-start="opacity-0 translate-y-4"
             x-transition:enter-end="opacity-100 translate-y-0">
            @if($tabbedListings[$key]->count())
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    @foreach($tabbedListings[$key] as $property)
                        @include('components.property-card', ['property' => $property])
                    @endforeach
                </div>
                <div class="text-center mt-10">
                    <a href="{{ $tab['route'] }}" class="btn-outline-gold px-8 py-3 rounded-xl font-semibold inline-flex items-center gap-2">
                        {{ __('View All') }} {{ $tab['label'] }}
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                    </a>
                </div>
            @else
                <div class="text-center py-16 text-gray-400">
                    <x-hicon :name="$tab['icon']" class="w-12 h-12 mx-auto mb-3 text-gray-300"/>
                    <p>{{ __('Coming soon — new listings added regularly.') }}</p>
                </div>
            @endif
        </div>
        @endforeach
    </div>
</section>
@endif

{{-- ===== WHY INVEST IN NORTH CYPRUS ===== --}}
@if($sec('section_why'))
<section class="py-20 bg-gray-950 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-14">
            <p class="text-gold text-sm font-semibold uppercase tracking-widest mb-2">{{ __('Investment') }}</p>
            <h2 class="font-heading text-3xl md:text-4xl font-bold">{{ __('Why Invest in North Cyprus?') }}</h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach([
                ['icon' => 'trophy', 'title' => __('High ROI'), 'desc' => __('Properties in North Cyprus offer 8-12% annual rental yield, one of the highest in the Mediterranean.')],
                ['icon' => 'cash', 'title' => __('Affordable Prices'), 'desc' => __('Property prices are 50-70% lower than comparable Mediterranean destinations like Cyprus South, Spain, or Greece.')],
                ['icon' => 'sun', 'title' => __('300+ Sunny Days'), 'desc' => __('North Cyprus enjoys over 300 sunny days per year, making it a year-round rental destination.')],
                ['icon' => 'waves', 'title' => __('Beautiful Coastline'), 'desc' => __('Crystal clear Mediterranean waters and pristine beaches attract thousands of tourists annually.')],
                ['icon' => 'document', 'title' => __('Easy Purchase Process'), 'desc' => __('Foreigners can easily purchase property in North Cyprus with minimal bureaucracy and low taxes.')],
                ['icon' => 'chart', 'title' => __('Growing Market'), 'desc' => __('The North Cyprus property market has seen consistent 15-20% annual growth over the past 5 years.')],
            ] as $item)
            <div class="bg-white/5 border border-white/10 rounded-2xl p-6 hover:border-gold/30 transition-all reveal">
                <div class="w-14 h-14 rounded-2xl bg-gold/10 flex items-center justify-center mb-4">
                    <x-hicon :name="$item['icon']" class="w-7 h-7 text-gold"/>
                </div>
                <h3 class="font-heading font-bold text-lg text-white mb-2">{{ $item['title'] }}</h3>
                <p class="text-sm text-gray-400 leading-relaxed">{{ $item['desc'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ===== FEATURED PROPERTIES ===== --}}
@if($sec('section_properties') && $featuredProperties->count())
<section class="py-20 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-end justify-between mb-12">
            <div>
                <p class="text-gold text-sm font-semibold uppercase tracking-widest mb-2">{{ __('Hand-Picked') }}</p>
                <h2 class="font-heading text-3xl md:text-4xl font-bold text-gray-900">{{ __('Featured Properties') }}</h2>
            </div>
            <a href="{{ route('properties.index') }}" class="hidden md:flex items-center space-x-2 text-gold font-semibold text-sm hover:underline">
                <span>{{ __('View All') }}</span>
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                </svg>
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($featuredProperties as $property)
            @include('components.property-card', ['property' => $property])
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ===== DAILY RENTALS ===== --}}
@if($sec('section_rentals') && $dailyRentals->count())
<section class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-end justify-between mb-12">
            <div>
                <p class="text-gold text-sm font-semibold uppercase tracking-widest mb-2">{{ __('Short Stay') }}</p>
                <h2 class="font-heading text-3xl md:text-4xl font-bold text-gray-900">{{ __('Daily Rentals') }}</h2>
                <p class="text-gray-500 mt-2">{{ __('Book your perfect holiday home in North Cyprus') }}</p>
            </div>
            <a href="{{ route('rentals.daily') }}" class="hidden md:flex items-center space-x-2 text-gold font-semibold text-sm hover:underline">
                <span>{{ __('View All') }}</span>
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                </svg>
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($dailyRentals as $rental)
            @include('components.rental-card', ['rental' => $rental])
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ===== TESTIMONIALS ===== --}}
@if($sec('section_testimonials') && $testimonials->count())
<section class="py-20 bg-gray-950 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-14">
            <p class="text-gold text-sm font-semibold uppercase tracking-widest mb-2">{{ __('Reviews') }}</p>
            <h2 class="font-heading text-3xl md:text-4xl font-bold">{{ __('What Our Clients Say') }}</h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($testimonials->take(6) as $testimonial)
            <div class="bg-white/5 border border-white/10 rounded-2xl p-6">
                <div class="flex items-center mb-4">
                    @for($i = 0; $i < 5; $i++)
                    <svg class="w-5 h-5 {{ $i < $testimonial->rating ? 'text-gold' : 'text-gray-600' }}" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                    @endfor
                </div>
                <p class="text-gray-300 text-sm leading-relaxed mb-5">"{{ $testimonial->content }}"</p>
                <div class="flex items-center space-x-3">
                    @if($testimonial->getFirstMediaUrl('avatar'))
                    <img src="{{ $testimonial->getFirstMediaUrl('avatar') }}" alt="{{ $testimonial->name }}"
                         class="w-10 h-10 rounded-full object-cover">
                    @else
                    <div class="w-10 h-10 rounded-full bg-gold/20 flex items-center justify-center">
                        <span class="text-gold font-bold text-sm">{{ substr($testimonial->name, 0, 1) }}</span>
                    </div>
                    @endif
                    <div>
                        <p class="text-white font-semibold text-sm">{{ $testimonial->name }}</p>
                        <p class="text-gray-500 text-xs">{{ $testimonial->nationality }}</p>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ===== LEAD FORM ===== --}}
@if($sec('section_lead'))
<section class="py-20 bg-gradient-to-br from-gray-900 to-black text-white">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <p class="text-gold text-sm font-semibold uppercase tracking-widest mb-2">{{ __('Free Consultation') }}</p>
        <h2 class="font-heading text-3xl md:text-4xl font-bold mb-4">{{ __('Interested in North Cyprus?') }}</h2>
        <p class="text-gray-400 mb-10">{{ __('Leave your details and our expert team will contact you within 24 hours.') }}</p>

        <form action="{{ route('leads.store') }}" method="POST"
              class="bg-white/5 backdrop-blur border border-white/10 rounded-2xl p-8"
              x-data="{ loading: false }" @submit.prevent="
                  loading = true;
                  fetch('{{ route('leads.store') }}', {
                      method: 'POST',
                      headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json'},
                      body: JSON.stringify(Object.fromEntries(new FormData($el)))
                  }).then(r => r.json()).then(d => {
                      loading = false;
                      if(d.success) $el.innerHTML = '<div class=\'text-center py-8\'><div class=\'text-5xl mb-4\'>✅</div><h3 class=\'text-2xl font-bold text-white mb-2\'>{{ __('Thank You!') }}</h3><p class=\'text-gray-400\'>{{ __('We will contact you within 24 hours.') }}</p></div>';
                  });
              ">
            @csrf
            <input type="hidden" name="type" value="contact">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <input type="text" name="name" placeholder="{{ __('Full Name') }} *" required
                       class="w-full bg-white/10 border border-white/20 rounded-xl px-4 py-3 text-white placeholder-gray-400 focus:outline-none focus:border-gold text-sm">
                <input type="tel" name="phone" placeholder="{{ __('Phone / WhatsApp') }} *" required
                       class="w-full bg-white/10 border border-white/20 rounded-xl px-4 py-3 text-white placeholder-gray-400 focus:outline-none focus:border-gold text-sm">
                <input type="email" name="email" placeholder="{{ __('Email Address') }}"
                       class="w-full bg-white/10 border border-white/20 rounded-xl px-4 py-3 text-white placeholder-gray-400 focus:outline-none focus:border-gold text-sm">
                <select name="source" class="w-full bg-white/10 border border-white/20 rounded-xl px-4 py-3 text-gray-400 focus:outline-none focus:border-gold text-sm">
                    <option value="website">{{ __('How did you find us?') }}</option>
                    <option value="google">Google</option>
                    <option value="facebook">Facebook</option>
                    <option value="instagram">Instagram</option>
                    <option value="referral">{{ __('Referral') }}</option>
                </select>
            </div>
            <textarea name="message" placeholder="{{ __('Your message or requirements...') }}" rows="3"
                      class="w-full bg-white/10 border border-white/20 rounded-xl px-4 py-3 text-white placeholder-gray-400 focus:outline-none focus:border-gold text-sm mb-4 resize-none"></textarea>
            <button type="submit" :disabled="loading"
                    class="btn-gold w-full md:w-auto px-10 py-4 rounded-xl font-semibold text-base flex items-center justify-center space-x-2 mx-auto">
                <span x-show="!loading">{{ __('Send Request') }}</span>
                <span x-show="loading">{{ __('Sending...') }}</span>
            </button>
        </form>
    </div>
</section>
@endif

{{-- ===== BLOG ===== --}}
@if($sec('section_blog') && $latestPosts->count())
<section class="py-20 bg-white reveal">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-end justify-between mb-12">
            <div>
                <p class="text-gold text-sm font-semibold uppercase tracking-widest mb-2">{{ __('Insights & Guides') }}</p>
                <h2 class="font-heading text-3xl md:text-4xl font-bold text-gray-900">{{ __('From Our Blog') }}</h2>
            </div>
            <a href="{{ route('blog.index') }}" class="hidden md:flex items-center space-x-2 text-gold font-semibold text-sm hover:underline">
                <span>{{ __('View All') }}</span>
                <x-hicon name="arrow-right" class="w-4 h-4"/>
            </a>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            @foreach($latestPosts as $post)
            <a href="{{ route('blog.show', $post->slug) }}" class="group block bg-white rounded-2xl overflow-hidden border border-gray-100 card-lift reveal">
                <div class="relative h-52 overflow-hidden">
                    @if($post->cover_image)<img src="{{ $post->cover_image }}" alt="{{ $post->title }}" class="w-full h-full object-cover img-zoom">@else<div class="w-full h-full bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center"><x-hicon name="document" class="w-12 h-12 text-gray-300"/></div>@endif
                    <span class="absolute top-3 left-3 bg-white/90 backdrop-blur text-gray-700 text-xs font-semibold px-2.5 py-1 rounded-full uppercase">{{ $post->category }}</span>
                </div>
                <div class="p-5">
                    <p class="text-xs text-gray-400 mb-2">{{ optional($post->published_at ?? $post->created_at)->format('M d, Y') }}</p>
                    <h3 class="font-heading font-bold text-lg text-gray-900 mb-2 line-clamp-2 group-hover:text-gold transition-colors">{{ $post->title }}</h3>
                    <p class="text-sm text-gray-500 line-clamp-2">{{ $post->translation()?->excerpt }}</p>
                </div>
            </a>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ===== FAQ ===== --}}
@if($sec('section_faq') && $faqs->count())
<section class="py-20 bg-white">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <p class="text-gold text-sm font-semibold uppercase tracking-widest mb-2">{{ __('Help') }}</p>
            <h2 class="font-heading text-3xl md:text-4xl font-bold text-gray-900">{{ __('Frequently Asked Questions') }}</h2>
        </div>

        <div class="space-y-4" x-data="{ active: null }">
            @foreach($faqs as $i => $faq)
            <div class="border border-gray-200 rounded-xl overflow-hidden">
                <button @click="active = active === {{ $i }} ? null : {{ $i }}"
                        class="w-full flex items-center justify-between px-6 py-4 text-left font-semibold text-gray-900 hover:bg-gray-50 transition-colors">
                    <span>{{ $faq->question }}</span>
                    <svg class="w-5 h-5 text-gold shrink-0 transition-transform" :class="active === {{ $i }} ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div x-show="active === {{ $i }}" x-transition class="px-6 pb-4 text-gray-600 text-sm leading-relaxed">
                    {{ $faq->answer }}
                </div>
            </div>
            @endforeach
        </div>

        <div class="text-center mt-8">
            <a href="{{ route('faq') }}" class="text-gold font-semibold hover:underline">{{ __('View All FAQs') }} →</a>
        </div>
    </div>
</section>
@endif

@endsection
