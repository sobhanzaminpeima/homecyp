@props(['steps'])

@php
    $currentIndex = collect($steps)->search(fn ($s) => $s['is_current']);
    $currentIndex = $currentIndex === false ? -1 : $currentIndex;
@endphp

<div class="mt-3 rounded-xl p-4 hc-surface border hc-shadow max-w-xl" style="border-radius: var(--hc-radius-card);" x-data="{ open: null }">
    <p class="hc-font-display font-medium mb-3" style="color: var(--hc-text);">{{ __('Your Journey') }}</p>

    <div class="flex items-center overflow-x-auto pb-2">
        @foreach($steps as $i => $step)
            @php $isPast = $currentIndex >= 0 && $i < $currentIndex; @endphp
            <div class="flex items-center shrink-0">
                <button @click="open = (open === '{{ $step['key'] }}' ? null : '{{ $step['key'] }}')"
                    class="hc-focusable flex flex-col items-center gap-1 px-2" style="min-width: 44px; min-height: 44px;">
                    <span class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-semibold"
                        style="{{ $step['is_current']
                            ? 'background: var(--hc-accent); color: var(--hc-accent-contrast); animation: hc-pulse 1.8s ease-in-out infinite;'
                            : ($isPast ? 'background: var(--hc-success); color: #fff;' : 'background: var(--hc-border); color: var(--hc-text-secondary);') }}">
                        @if($isPast)
                            <x-chat.icon name="check-circle" :size="14" />
                        @else
                            {{ $i + 1 }}
                        @endif
                    </span>
                    <span class="text-xs whitespace-nowrap" style="color: {{ $step['is_current'] ? 'var(--hc-accent)' : 'var(--hc-text-secondary)' }}; font-weight: {{ $step['is_current'] ? '600' : '400' }};">
                        {{ $step['label'] }}
                    </span>
                </button>
                @if(!$loop->last)<div class="w-8 h-px mx-1" style="background: var(--hc-border);"></div>@endif
            </div>
        @endforeach
    </div>

    @foreach($steps as $step)
        <div x-show="open === '{{ $step['key'] }}'" x-collapse class="text-sm mt-2 p-3 rounded-lg border hc-border" style="background: var(--hc-bg);">
            <p style="color: var(--hc-text);">{{ $step['description'] }}</p>
            @if($step['next_action'])<p class="text-xs mt-1 font-medium hc-accent-text">{{ __('Next') }}: {{ $step['next_action'] }}</p>@endif
        </div>
    @endforeach
</div>
