@php $type = $widget['type'] ?? null; @endphp

@if($type === 'roi')
    <div class="mt-3 rounded-xl p-4 hc-surface border hc-shadow max-w-md" style="border-radius: var(--hc-radius-card);">
        <p class="hc-font-display font-medium mb-2 flex items-center gap-1.5" style="color: var(--hc-text);">
            <x-chat.icon name="trending-up" :size="18" /> {{ __('ROI Estimate') }} — {{ $widget['property_title'] ?? '' }}
        </p>
        @if(!($widget['available'] ?? false))
            <p class="text-sm hc-text-secondary">{{ $widget['message'] }}</p>
        @else
            <div class="grid grid-cols-2 gap-3 text-sm hc-tabular">
                <div><p class="hc-text-secondary">{{ __('Price') }}</p><p class="font-semibold">{{ $widget['currency'] }} {{ number_format($widget['price']) }}</p></div>
                <div><p class="hc-text-secondary">{{ __('Est. annual rental income') }}</p><p class="font-semibold">{{ $widget['currency'] }} {{ number_format($widget['annual_rental_income']) }}</p></div>
                <div><p class="hc-text-secondary">{{ __('ROI') }}</p><p class="font-semibold" style="color: var(--hc-success);">{{ $widget['roi_percent'] }}%</p></div>
                <div><p class="hc-text-secondary">{{ __('Payback period') }}</p><p class="font-semibold">{{ $widget['payback_years'] }} {{ __('years') }}</p></div>
            </div>
        @endif
    </div>

@elseif($type === 'mortgage')
    <div class="mt-3 rounded-xl p-4 hc-surface border hc-shadow max-w-md" style="border-radius: var(--hc-radius-card);">
        <p class="hc-font-display font-medium mb-2" style="color: var(--hc-text);">🏦 {{ __('Mortgage Estimate') }}</p>
        <div class="grid grid-cols-2 gap-3 text-sm hc-tabular">
            <div><p class="hc-text-secondary">{{ __('Property price') }}</p><p class="font-semibold">{{ number_format($widget['price']) }}</p></div>
            <div><p class="hc-text-secondary">{{ __('Down payment') }}</p><p class="font-semibold">{{ number_format($widget['down_payment']) }}</p></div>
            <div><p class="hc-text-secondary">{{ __('Loan amount') }}</p><p class="font-semibold">{{ number_format($widget['loan_amount']) }}</p></div>
            <div><p class="hc-text-secondary">{{ __('Interest rate') }}</p><p class="font-semibold">{{ $widget['interest_rate'] }}%</p></div>
            <div><p class="hc-text-secondary">{{ __('Term') }}</p><p class="font-semibold">{{ $widget['term_years'] }} {{ __('years') }}</p></div>
            <div><p class="hc-text-secondary">{{ __('Monthly payment') }}</p><p class="font-semibold hc-accent-text">{{ number_format($widget['monthly_payment']) }}</p></div>
            <div class="col-span-2"><p class="hc-text-secondary">{{ __('Total interest over term') }}</p><p class="font-semibold">{{ number_format($widget['total_interest']) }}</p></div>
        </div>
    </div>

@elseif($type === 'residency')
    <div class="mt-3 rounded-xl p-4 hc-surface border hc-shadow max-w-md" style="border-radius: var(--hc-radius-card);">
        <p class="hc-font-display font-medium mb-2" style="color: var(--hc-text);">🛂 {{ __('Residency Advisor') }}</p>
        @if(!($widget['available'] ?? false))
            <p class="text-sm hc-text-secondary">{{ $widget['message'] }}</p>
        @else
            <ol class="text-sm space-y-2 list-decimal list-inside" style="color: var(--hc-text);">
                @foreach($widget['checklist'] as $item)
                    <li>{{ $item['text'] }}</li>
                @endforeach
            </ol>
        @endif
    </div>

@elseif($type === 'area_advisor')
    <div class="mt-3 rounded-xl p-4 hc-surface border hc-shadow max-w-md" style="border-radius: var(--hc-radius-card);">
        <p class="hc-font-display font-medium mb-2 flex items-center gap-1.5" style="color: var(--hc-text);">
            <x-chat.icon name="map-pin" :size="18" /> {{ __('Area Advisor') }}
        </p>
        <div class="space-y-3">
            @foreach(($widget['recommendations'] ?? []) as $rec)
                <div>
                    <p class="font-semibold text-sm" style="color: var(--hc-text);">{{ $rec['name'] ?? '' }}</p>
                    @if(!empty($rec['overview']))<p class="text-xs hc-text-secondary mt-0.5">{{ $rec['overview'] }}</p>@endif
                    @if(!empty($rec['reasons']))
                        <ul class="text-xs mt-1 list-disc list-inside" style="color: var(--hc-text);">
                            @foreach($rec['reasons'] as $reason)<li>{{ $reason }}</li>@endforeach
                        </ul>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

@elseif($type === 'compare')
    <x-chat.compare-table :rows="$widget['rows']" />

@elseif($type === 'timeline')
    <x-chat.timeline :steps="$widget['steps']" />
@endif
