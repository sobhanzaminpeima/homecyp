<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'HomeCyp') | {{ __('Luxury Real Estate & Daily Rentals in North Cyprus') }}</title>
    <meta name="description" content="@yield('meta_description', __('Premium investment opportunities, luxury residences, and exclusive Airbnb rentals in North Cyprus.'))">
    <meta name="keywords" content="@yield('meta_keywords', 'North Cyprus real estate, luxury properties, daily rentals, investment, Kyrenia, Famagusta')">

    {{-- Open Graph --}}
    <meta property="og:title" content="@yield('title', 'HomeCyp')">
    <meta property="og:description" content="@yield('meta_description', __('Premium real estate in North Cyprus'))">
    <meta property="og:image" content="@yield('og_image', asset('images/og-default.jpg'))">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">

    {{-- Twitter --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('title', 'HomeCyp')">
    <meta name="twitter:description" content="@yield('meta_description', __('Premium real estate in North Cyprus'))">

    <link rel="canonical" href="{{ url()->current() }}">

    {{-- Favicon --}}
    @php $faviconPath = \App\Models\SiteSetting::get('favicon_path'); @endphp
    <link rel="icon" href="{{ $faviconPath ? \Illuminate\Support\Facades\Storage::disk('public')->url($faviconPath) : asset('images/logo.svg') }}">

    {{-- Performance: preconnect + dns-prefetch --}}
    <link rel="dns-prefetch" href="https://fonts.gstatic.com">

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    {{-- Styles --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')

    {{-- Organization + Website Schema --}}
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "RealEstateAgent",
        "name": "HomeCyp",
        "description": "{{ __('Luxury Real Estate & Daily Rentals in North Cyprus') }}",
        "url": "{{ url('/') }}",
        "logo": "{{ asset('images/logo.svg') }}",
        "telephone": "+905338456497",
        "areaServed": "North Cyprus",
        "address": {
            "@@type": "PostalAddress",
            "addressRegion": "North Cyprus",
            "addressCountry": "CY"
        }
    }
    </script>

    {{-- Google Analytics --}}
    @if($gaId = \App\Models\SiteSetting::get('google_analytics_id'))
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ $gaId }}"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', '{{ $gaId }}');
    </script>
    @endif

    {{-- Meta Pixel --}}
    @if($pixelId = \App\Models\SiteSetting::get('meta_pixel_id'))
    <script>
        !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
        n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
        n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
        t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,
        document,'script','https://connect.facebook.net/en_US/fbevents.js');
        fbq('init', '{{ $pixelId }}'); fbq('track', 'PageView');
    </script>
    @endif

    {{-- Google reCAPTCHA --}}
    @if(\App\Services\RecaptchaService::enabled())
    <script src="https://www.google.com/recaptcha/api.js?render={{ \App\Services\RecaptchaService::siteKey() }}"></script>
    @endif

    @php $accent = \App\Models\SiteSetting::get('theme_accent', '#C9A84C'); @endphp
    <style>
        :root {
            --color-gold: {{ $accent }};
            --color-gold-light: #E8C97A;
            --color-dark: #0A0A0A;
            --color-dark-2: #1A1A1A;
            --color-gray: #6B7280;
            --font-heading: 'Playfair Display', serif;
            --font-body: 'Inter', sans-serif;
        }
        @if($accent !== '#C9A84C')
        .text-gold { color: {{ $accent }} !important; }
        .bg-gold { background-color: {{ $accent }} !important; }
        .border-gold { border-color: {{ $accent }} !important; }
        .btn-gold { background: linear-gradient(135deg, {{ $accent }}, {{ $accent }}cc) !important; }
        .text-gradient-gold { background: linear-gradient(135deg, {{ $accent }}, #E8C97A) !important; -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent; }
        @endif
        body { font-family: var(--font-body); }
        .font-heading { font-family: var(--font-heading); }
        .text-gold { color: var(--color-gold); }
        .bg-gold { background-color: var(--color-gold); }
        .border-gold { border-color: var(--color-gold); }
        .btn-gold {
            background: linear-gradient(135deg, var(--color-gold), var(--color-gold-light));
            color: #fff;
            transition: all 0.3s ease;
        }
        .btn-gold:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(201,168,76,0.4); }
        .btn-outline-gold {
            border: 2px solid var(--color-gold);
            color: var(--color-gold);
            transition: all 0.3s ease;
        }
        .btn-outline-gold:hover { background: var(--color-gold); color: #fff; }
    </style>
</head>
<body class="bg-white text-gray-900 antialiased">

    {{-- Header --}}
    @include('components.header')

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="fixed top-20 right-4 z-50 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg"
             x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)">
            {{ session('success') }}
        </div>
    @endif

    {{-- Main Content --}}
    <main>
        @yield('content')
    </main>

    {{-- Footer --}}
    @include('components.footer')

    {{-- WhatsApp Float Button --}}
    <a href="https://wa.me/905338456497" target="_blank" rel="noopener"
       class="fixed bottom-6 right-6 z-50 w-14 h-14 bg-green-500 rounded-full flex items-center justify-center shadow-lg hover:bg-green-600 transition-all hover:scale-110"
       title="WhatsApp">
        <svg class="w-7 h-7 text-white" fill="currentColor" viewBox="0 0 24 24">
            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
        </svg>
    </a>

    @stack('scripts')
</body>
</html>
