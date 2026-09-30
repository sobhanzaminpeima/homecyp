<?php

namespace App\Filament\Pages;

use App\Models\Conversation;
use App\Models\Message;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class AiAnalytics extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationGroup = 'AI';
    protected static ?string $navigationLabel = 'Analytics';
    protected static ?int $navigationSort = 5;
    protected static ?string $title = 'Conversation Analytics';
    protected static string $view = 'filament.pages.ai-analytics';

    public function getStats(): array
    {
        $totalConversations = Conversation::count();
        $totalMessages = Message::count();
        $withLead = Conversation::whereNotNull('lead_id')->count();

        // Drop-off: conversations that never got past a single user message.
        $droppedOff = Conversation::has('messages', '<=', 1)->count();

        return [
            'total_conversations' => $totalConversations,
            'total_messages' => $totalMessages,
            'leads_captured' => $withLead,
            'conversion_rate' => $totalConversations > 0 ? round($withLead / $totalConversations * 100, 1) : 0,
            'single_message_dropoff' => $droppedOff,
            'negative_feedback' => Message::where('feedback', -1)->count(),
            'positive_feedback' => Message::where('feedback', 1)->count(),
        ];
    }

    public function getTopIntents()
    {
        return Message::query()
            ->select('intent', DB::raw('count(*) as total'))
            ->whereNotNull('intent')
            ->groupBy('intent')
            ->orderByDesc('total')
            ->limit(10)
            ->get();
    }

    public function getTopProperties()
    {
        // property_cards is a JSON array column; tally frequency in PHP since the
        // dataset is small enough that a DB-side JSON_TABLE isn't worth the MySQL
        // version risk on a shared cPanel host.
        $counts = [];

        Message::whereNotNull('property_cards')->pluck('property_cards')->each(function ($ids) use (&$counts) {
            foreach ((array) $ids as $id) {
                $counts[$id] = ($counts[$id] ?? 0) + 1;
            }
        });

        arsort($counts);
        $top = array_slice($counts, 0, 10, true);

        $properties = \App\Models\Property::with('translations')->whereIn('id', array_keys($top))->get()->keyBy('id');

        return collect($top)->map(fn ($count, $id) => [
            'title' => $properties[$id]->title ?? "#{$id}",
            'count' => $count,
        ])->values();
    }

    public function getNegativeFeedbackMessages()
    {
        return Message::where('feedback', -1)->latest()->limit(15)->with('conversation')->get();
    }
}
