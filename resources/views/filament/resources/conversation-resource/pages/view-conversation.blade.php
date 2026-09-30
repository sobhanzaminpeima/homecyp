<x-filament-panels::page>
    <div class="space-y-4">
        @foreach($this->getMessages() as $message)
            <div class="p-4 rounded-xl {{ $message->role === 'user' ? 'bg-primary-50 ml-12' : 'bg-gray-50 mr-12' }}">
                <div class="flex items-center justify-between mb-1">
                    <span class="text-xs font-semibold uppercase text-gray-500">{{ $message->role }}</span>
                    <div class="flex items-center gap-2 text-xs text-gray-400">
                        @if($message->intent)<span class="px-2 py-0.5 bg-gray-200 rounded-full">{{ $message->intent }}</span>@endif
                        @if($message->feedback === 1)<span>👍</span>@endif
                        @if($message->feedback === -1)<span>👎</span>@endif
                        <span>{{ $message->created_at->format('Y-m-d H:i') }}</span>
                    </div>
                </div>
                <p class="whitespace-pre-line text-sm">{{ $message->content }}</p>
                @if(!empty($message->property_cards))
                    <p class="text-xs text-gray-400 mt-2">Properties shown: {{ implode(', ', $message->property_cards) }}</p>
                @endif
            </div>
        @endforeach
    </div>
</x-filament-panels::page>
