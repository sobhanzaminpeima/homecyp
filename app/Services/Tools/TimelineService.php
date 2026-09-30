<?php

namespace App\Services\Tools;

use App\Models\Conversation;
use App\Models\TimelineStep;

class TimelineService
{
    public function build(Conversation $conversation, ?int $projectId = null): array
    {
        $steps = TimelineStep::where('project_id', $projectId)
            ->orderBy('sort_order')
            ->get();

        if ($steps->isEmpty() && $projectId !== null) {
            // Fall back to the default global timeline if this project has no override.
            $steps = TimelineStep::whereNull('project_id')->orderBy('sort_order')->get();
        }

        $currentKey = $this->currentStepKey($conversation);

        return [
            'type' => 'timeline',
            'current_step' => $currentKey,
            'steps' => $steps->map(fn (TimelineStep $s) => [
                'key' => $s->key,
                'label' => $s->label,
                'description' => $s->description,
                'next_action' => $s->next_action,
                'is_current' => $s->key === $currentKey,
            ])->values()->all(),
        ];
    }

    protected function currentStepKey(Conversation $conversation): string
    {
        $memory = $conversation->memory ?? [];

        if (!empty($memory['reserved_property_id'])) {
            return 'reserve';
        }

        $hasViewedProperty = $conversation->messages()
            ->whereNotNull('property_cards')
            ->whereJsonLength('property_cards', '>', 0)
            ->exists();

        if ($hasViewedProperty) {
            return 'choose_property';
        }

        return 'choose_area';
    }
}
