<x-filament-panels::page>
    @php $stats = $this->getStats(); @endphp

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="p-4 bg-white rounded-xl border">
            <p class="text-xs text-gray-500">Conversations</p>
            <p class="text-2xl font-semibold">{{ $stats['total_conversations'] }}</p>
        </div>
        <div class="p-4 bg-white rounded-xl border">
            <p class="text-xs text-gray-500">Messages</p>
            <p class="text-2xl font-semibold">{{ $stats['total_messages'] }}</p>
        </div>
        <div class="p-4 bg-white rounded-xl border">
            <p class="text-xs text-gray-500">Lead conversion rate</p>
            <p class="text-2xl font-semibold">{{ $stats['conversion_rate'] }}%</p>
            <p class="text-xs text-gray-400">{{ $stats['leads_captured'] }} leads captured</p>
        </div>
        <div class="p-4 bg-white rounded-xl border">
            <p class="text-xs text-gray-500">Single-message drop-off</p>
            <p class="text-2xl font-semibold">{{ $stats['single_message_dropoff'] }}</p>
        </div>
        <div class="p-4 bg-white rounded-xl border">
            <p class="text-xs text-gray-500">👍 Positive feedback</p>
            <p class="text-2xl font-semibold">{{ $stats['positive_feedback'] }}</p>
        </div>
        <div class="p-4 bg-white rounded-xl border">
            <p class="text-xs text-gray-500">👎 Negative feedback</p>
            <p class="text-2xl font-semibold">{{ $stats['negative_feedback'] }}</p>
        </div>
    </div>

    <div class="grid md:grid-cols-2 gap-4 mt-6">
        <div class="p-4 bg-white rounded-xl border">
            <p class="font-medium mb-3">Most common intents</p>
            <div class="space-y-2">
                @forelse($this->getTopIntents() as $row)
                    <div class="flex justify-between text-sm"><span>{{ $row->intent }}</span><span class="text-gray-500">{{ $row->total }}</span></div>
                @empty
                    <p class="text-sm text-gray-400">No data yet.</p>
                @endforelse
            </div>
        </div>

        <div class="p-4 bg-white rounded-xl border">
            <p class="font-medium mb-3">Most shown properties</p>
            <div class="space-y-2">
                @forelse($this->getTopProperties() as $row)
                    <div class="flex justify-between text-sm"><span>{{ $row['title'] }}</span><span class="text-gray-500">{{ $row['count'] }}</span></div>
                @empty
                    <p class="text-sm text-gray-400">No data yet.</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="p-4 bg-white rounded-xl border mt-6">
        <p class="font-medium mb-3">Recent 👎 feedback</p>
        <div class="space-y-3">
            @forelse($this->getNegativeFeedbackMessages() as $message)
                <div class="text-sm border-b border-gray-100 pb-2">
                    <p class="text-gray-700">{{ \Illuminate\Support\Str::limit($message->content, 160) }}</p>
                    <p class="text-xs text-gray-400 mt-1">{{ $message->created_at->diffForHumans() }} · conversation #{{ $message->conversation_id }}</p>
                </div>
            @empty
                <p class="text-sm text-gray-400">No negative feedback yet.</p>
            @endforelse
        </div>
    </div>
</x-filament-panels::page>
