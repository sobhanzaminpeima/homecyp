<header class="fixed top-0 left-0 right-0 z-50 transition-all duration-300" x-data="{ open: false, scrolled: false }"
        x-init="window.addEventListener('scroll', () => scrolled = window.scrollY > 50)"
        :class="scrolled ? 'bg-white shadow-lg' : 'bg-transparent'">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-20">

            {{-- Logo (uploaded via Admin → Logo & Branding, else default) --}}
            @php $logoPath = \App\Models\SiteSetting::get('logo_path'); @endphp
            <a href="{{ route('home') }}" class="flex items-center">
                <img src="{{ $logoPath ? \Illuminate\Support\Facades\Storage::disk('public')->url($logoPath) : asset('images/logo.svg') }}" alt="HomeCyp" class="h-10 w-auto" width="140" height="40">
            </a>

            {{-- Desktop Navigation (header items managed via Admin → Menus) --}}
            @php $headerMenu = \App\Models\MenuItem::forLocation('header'); @endphp
            <nav class="hidden lg:flex items-center space-x-1">
                @if($headerMenu->count())
                    @foreach($headerMenu as $item)
                    <a href="{{ $item->url }}" class="nav-link px-4 py-2 text-sm font-medium transition-colors hover:text-gold"
                       :class="scrolled ? 'text-gray-800' : 'text-white'">{{ __($item->label) }}</a>
                    @endforeach
                @else
                <a href="{{ route('home') }}" class="nav-link px-4 py-2 text-sm font-medium transition-colors hover:text-gold"
                   :class="scrolled ? 'text-gray-800' : 'text-white'">{{ __('Home') }}</a>
                <a href="{{ route('projects.index') }}" class="nav-link px-4 py-2 text-sm font-medium transition-colors hover:text-gold"
                   :class="scrolled ? 'text-gray-800' : 'text-white'">{{ __('Projects') }}</a>
                <a href="{{ route('resale.index') }}" class="nav-link px-4 py-2 text-sm font-medium transition-colors hover:text-gold"
                   :class="scrolled ? 'text-gray-800' : 'text-white'">{{ __('Resale') }}</a>
                @endif

                {{-- Rentals Dropdown (hover + click, hidden until Alpine loads) --}}
                <div class="relative" x-data="{ ropen: false }" @mouseenter="ropen = true" @mouseleave="ropen = false">
                    <button @click="ropen = !ropen"
                            class="flex items-center px-4 py-2 text-sm font-medium transition-colors hover:text-gold"
                            :class="scrolled ? 'text-gray-800' : 'text-white'">
                        {{ __('Rentals') }}
                        <svg class="ml-1 w-4 h-4 transition-transform" :class="ropen ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div x-show="ropen" x-cloak
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 translate-y-0"
                         x-transition:leave-end="opacity-0 translate-y-1"
                         class="absolute top-full left-0 pt-2 w-56 z-50">
                        <div class="bg-white rounded-xl shadow-2xl border border-gray-100 py-2 overflow-hidden">
                            <a href="{{ route('rentals.daily') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gold/5 hover:text-gold transition-colors">
                                <x-hicon name="beach" class="w-5 h-5 text-gold"/> {{ __('Daily Rentals') }}
                            </a>
                            <a href="{{ route('rentals.longterm') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gold/5 hover:text-gold transition-colors">
                                <x-hicon name="home" class="w-5 h-5 text-gold"/> {{ __('Long-Term Rentals') }}
                            </a>
                        </div>
                    </div>
                </div>

                @unless($headerMenu->count())
                <a href="{{ route('investment-guide') }}" class="nav-link px-4 py-2 text-sm font-medium transition-colors hover:text-gold"
                   :class="scrolled ? 'text-gray-800' : 'text-white'">{{ __('Invest') }}</a>
                <a href="{{ route('blog.index') }}" class="nav-link px-4 py-2 text-sm font-medium transition-colors hover:text-gold"
                   :class="scrolled ? 'text-gray-800' : 'text-white'">{{ __('Blog') }}</a>
                <a href="{{ route('about') }}" class="nav-link px-4 py-2 text-sm font-medium transition-colors hover:text-gold"
                   :class="scrolled ? 'text-gray-800' : 'text-white'">{{ __('About') }}</a>
                <a href="{{ route('contact') }}" class="nav-link px-4 py-2 text-sm font-medium transition-colors hover:text-gold"
                   :class="scrolled ? 'text-gray-800' : 'text-white'">{{ __('Contact') }}</a>
                @endunless
            </nav>

            {{-- Right Side --}}
            <div class="flex items-center space-x-3">
                {{-- Language Switcher --}}
                <div class="flex items-center space-x-1 border rounded-lg overflow-hidden" :class="scrolled ? 'border-gray-300' : 'border-white/40'">
                    <a href="{{ route('lang.switch', 'en') }}"
                       class="px-3 py-1.5 text-xs font-semibold transition-colors {{ app()->getLocale() === 'en' ? 'bg-gold text-white' : '' }}"
                       :class="scrolled ? 'text-gray-700' : 'text-white'">EN</a>
                    <a href="{{ route('lang.switch', 'tr') }}"
                       class="px-3 py-1.5 text-xs font-semibold transition-colors {{ app()->getLocale() === 'tr' ? 'bg-gold text-white' : '' }}"
                       :class="scrolled ? 'text-gray-700' : 'text-white'">TR</a>
                </div>

                {{-- CTA Button --}}
                <a href="tel:+905338456497"
                   class="hidden md:flex items-center space-x-2 btn-gold px-4 py-2 rounded-lg text-sm font-semibold">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                    </svg>
                    <span>+90 533 845 64 97</span>
                </a>

                {{-- Mobile Menu Button --}}
                <button @click="open = !open" class="lg:hidden p-2 rounded-lg"
                        :class="scrolled ? 'text-gray-800' : 'text-white'">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path x-show="!open" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        <path x-show="open" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    {{-- Mobile Menu --}}
    <div x-show="open" x-cloak x-transition class="lg:hidden bg-white border-t border-gray-100 shadow-xl">
        <nav class="px-4 py-4 space-y-1">
            <a href="{{ route('home') }}" class="block px-4 py-2.5 text-gray-800 font-medium rounded-lg hover:bg-gray-50">{{ __('Home') }}</a>
            <a href="{{ route('projects.index') }}" class="block px-4 py-2.5 text-gray-800 font-medium rounded-lg hover:bg-gray-50">{{ __('Projects') }}</a>
            <a href="{{ route('resale.index') }}" class="block px-4 py-2.5 text-gray-800 font-medium rounded-lg hover:bg-gray-50">{{ __('Resale') }}</a>
            <a href="{{ route('rentals.daily') }}" class="block px-4 py-2.5 text-gray-800 font-medium rounded-lg hover:bg-gray-50">{{ __('Daily Rentals') }}</a>
            <a href="{{ route('rentals.longterm') }}" class="block px-4 py-2.5 text-gray-800 font-medium rounded-lg hover:bg-gray-50">{{ __('Long-Term Rentals') }}</a>
            <a href="{{ route('investment-guide') }}" class="block px-4 py-2.5 text-gray-800 font-medium rounded-lg hover:bg-gray-50">{{ __('Investment Guide') }}</a>
            <a href="{{ route('about') }}" class="block px-4 py-2.5 text-gray-800 font-medium rounded-lg hover:bg-gray-50">{{ __('About') }}</a>
            <a href="{{ route('contact') }}" class="block px-4 py-2.5 text-gray-800 font-medium rounded-lg hover:bg-gray-50">{{ __('Contact') }}</a>
            <a href="tel:+905338456497" class="block btn-gold text-center py-3 rounded-lg font-semibold mt-2">+90 533 845 64 97</a>
        </nav>
    </div>
</header>
