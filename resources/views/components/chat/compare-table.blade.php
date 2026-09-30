@props(['rows'])

{{-- Desktop: real table. Mobile: vertical swipeable cards — a horizontal table
     doesn't work at phone widths (design spec 6). --}}
<div class="mt-3">
    <div class="hidden sm:block rounded-xl overflow-x-auto border hc-border hc-shadow" style="border-radius: var(--hc-radius-card);">
        <table class="w-full text-sm hc-tabular">
            <thead style="background: var(--hc-bg);">
                <tr>
                    <th class="text-left px-3 py-2" style="color: var(--hc-text-secondary);">{{ __('Property') }}</th>
                    <th class="text-left px-3 py-2" style="color: var(--hc-text-secondary);">{{ __('Price') }}</th>
                    <th class="text-left px-3 py-2" style="color: var(--hc-text-secondary);">{{ __('Region') }}</th>
                    <th class="text-left px-3 py-2" style="color: var(--hc-text-secondary);">{{ __('Bed') }}</th>
                    <th class="text-left px-3 py-2" style="color: var(--hc-text-secondary);">{{ __('Area') }}</th>
                    <th class="text-left px-3 py-2" style="color: var(--hc-text-secondary);">{{ __('ROI') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rows as $row)
                    <tr class="border-t hc-border" style="color: var(--hc-text);">
                        <td class="px-3 py-2 font-medium">{{ $row['title'] }}</td>
                        <td class="px-3 py-2">{{ $row['currency'] }} {{ number_format($row['price']) }}</td>
                        <td class="px-3 py-2">{{ $row['region'] }}</td>
                        <td class="px-3 py-2">{{ $row['bedrooms'] }}</td>
                        <td class="px-3 py-2">{{ $row['area'] }} {{ $row['area_unit'] }}</td>
                        <td class="px-3 py-2" style="{{ $row['roi_estimate'] ? 'color: var(--hc-success);' : '' }}">{{ $row['roi_estimate'] ? $row['roi_estimate'].'%' : '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="sm:hidden flex gap-3 overflow-x-auto pb-2 snap-x snap-mandatory">
        @foreach($rows as $row)
            <div class="shrink-0 w-64 snap-start rounded-xl border hc-border hc-surface hc-shadow p-4" style="border-radius: var(--hc-radius-card);">
                <p class="font-medium" style="color: var(--hc-text);">{{ $row['title'] }}</p>
                <dl class="mt-2 space-y-1 text-sm">
                    <div class="flex justify-between"><dt class="hc-text-secondary">{{ __('Price') }}</dt><dd class="hc-tabular">{{ $row['currency'] }} {{ number_format($row['price']) }}</dd></div>
                    <div class="flex justify-between"><dt class="hc-text-secondary">{{ __('Region') }}</dt><dd>{{ $row['region'] }}</dd></div>
                    <div class="flex justify-between"><dt class="hc-text-secondary">{{ __('Bed') }}</dt><dd>{{ $row['bedrooms'] }}</dd></div>
                    <div class="flex justify-between"><dt class="hc-text-secondary">{{ __('Area') }}</dt><dd>{{ $row['area'] }} {{ $row['area_unit'] }}</dd></div>
                    <div class="flex justify-between"><dt class="hc-text-secondary">{{ __('ROI') }}</dt><dd style="{{ $row['roi_estimate'] ? 'color: var(--hc-success);' : '' }}">{{ $row['roi_estimate'] ? $row['roi_estimate'].'%' : '—' }}</dd></div>
                </dl>
            </div>
        @endforeach
    </div>
</div>
