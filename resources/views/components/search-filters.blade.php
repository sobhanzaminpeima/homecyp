<section class="bg-white border-b border-gray-200 sticky top-20 z-30 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
        <form action="{{ route('properties.search') }}" method="GET" class="grid grid-cols-2 md:grid-cols-6 gap-3">
            <select name="type" class="px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:border-gold outline-none">
                <option value="">{{ __('Property Type') }}</option>
                <option value="apartment" @selected(request('type')=='apartment')>{{ __('Apartment') }}</option>
                <option value="villa" @selected(request('type')=='villa')>{{ __('Villa') }}</option>
                <option value="penthouse" @selected(request('type')=='penthouse')>{{ __('Penthouse') }}</option>
                <option value="studio" @selected(request('type')=='studio')>{{ __('Studio') }}</option>
            </select>
            <select name="bedrooms" class="px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:border-gold outline-none">
                <option value="">{{ __('Bedrooms') }}</option>
                @for($i=1;$i<=5;$i++)
                <option value="{{ $i }}" @selected(request('bedrooms')==$i)>{{ $i }}+</option>
                @endfor
            </select>
            <input type="number" name="price_min" value="{{ request('price_min') }}" placeholder="{{ __('Min Price') }}"
                   class="px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:border-gold outline-none">
            <input type="number" name="price_max" value="{{ request('price_max') }}" placeholder="{{ __('Max Price') }}"
                   class="px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:border-gold outline-none">
            <input type="text" name="location" value="{{ request('location') }}" placeholder="{{ __('Location...') }}"
                   class="px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:border-gold outline-none">
            <button type="submit" class="btn-gold py-2.5 rounded-lg font-semibold text-sm">{{ __('Search') }}</button>
        </form>
    </div>
</section>
