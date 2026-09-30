@props(['action', 'variant' => 'accent'])

<button wire:click="{{ $action }}" type="button"
    class="hc-focusable {{ $variant === 'accent' ? 'hc-chip' : 'hc-chip-light' }}">
    {{ $slot }}
</button>
