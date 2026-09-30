@props(['role'])

@if($role === 'user')
    <div data-message-role="user" {{ $attributes->merge(['class' => 'hc-fade-rise-msg px-4 py-3 max-w-lg']) }}
         style="background: var(--hc-primary); color: var(--hc-primary-contrast); border-radius: var(--hc-radius-bubble) var(--hc-radius-bubble) 6px var(--hc-radius-bubble);">
        {{ $slot }}
    </div>
@else
    <div data-message-role="assistant" {{ $attributes->merge(['class' => 'hc-fade-rise-msg px-4 py-3 border']) }}
         style="background: var(--hc-surface); color: var(--hc-text); border-color: var(--hc-border); border-radius: var(--hc-radius-bubble) var(--hc-radius-bubble) var(--hc-radius-bubble) 6px;">
        {{ $slot }}
    </div>
@endif
