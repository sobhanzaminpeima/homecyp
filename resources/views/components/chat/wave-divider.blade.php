{{--
    Signature element (design spec 3.5): a subtle wave, standing in for the
    Kyrenia coastline, used sparingly — only at 2-3 key points (empty-state
    hero, lead capture modal), never as generic page decoration.
--}}
@props(['color' => 'var(--hc-accent)', 'class' => ''])

<svg viewBox="0 0 400 24" preserveAspectRatio="none" class="{{ $class }}" aria-hidden="true">
    <path d="M0 12 C 50 22, 100 2, 150 12 S 250 22, 300 12 S 375 2, 400 12"
          fill="none" stroke="{{ $color }}" stroke-width="1.5" stroke-linecap="round" opacity="0.5" />
</svg>
