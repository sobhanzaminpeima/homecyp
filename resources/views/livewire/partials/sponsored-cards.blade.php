@php
    $propertyIds = collect($cards)->pluck('property_id');
    $properties = \App\Models\Property::with('translations')->whereIn('id', $propertyIds)->get()->keyBy('id');
@endphp

@if($properties->isNotEmpty())
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-3">
        @foreach($cards as $card)
            @php $property = $properties[$card['property_id']] ?? null; @endphp
            @continue(!$property)
            <x-chat.property-card :property="$property" :sponsored="true" :sponsor-click-url="route('sponsor.click', $card['campaign_id'])" />
        @endforeach
    </div>
@endif
