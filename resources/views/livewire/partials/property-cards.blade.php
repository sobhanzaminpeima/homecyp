@php
    $properties = \App\Models\Property::with(['translations', 'media'])->whereIn('id', $ids)->get();
@endphp

@if($properties->isNotEmpty())
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-3">
        @foreach($properties as $property)
            <x-chat.property-card :property="$property" />
        @endforeach
    </div>
@endif
