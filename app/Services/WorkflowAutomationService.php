<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\Lead;
use App\Models\Property;
use App\Models\User;
use App\Models\ViewingRequest;
use App\Services\Mcp\SmtpEmailConnector;

/**
 * Automates what happens after a user asks to book a viewing (spec section 11):
 * create the internal "task" (a ViewingRequest assigned to an agent), record it
 * against the lead, and send a confirmation — via the MCP connector layer so a
 * future external CRM/calendar swap doesn't touch this orchestration logic.
 */
class WorkflowAutomationService
{
    public function __construct(protected SmtpEmailConnector $emailConnector)
    {
    }

    public function requestViewing(Lead $lead, ?Property $property, ?Conversation $conversation): ViewingRequest
    {
        $agent = $this->assignAgent($property);

        $viewingRequest = ViewingRequest::create([
            'lead_id' => $lead->id,
            'property_id' => $property?->id,
            'agent_id' => $agent?->id,
            'conversation_id' => $conversation?->id,
            'status' => 'pending',
            'requested_at' => now(),
        ]);

        if ($lead->email) {
            $this->emailConnector->send('viewing_confirmation', [
                'viewing_request' => $viewingRequest,
                'email' => $lead->email,
            ]);
        }

        return $viewingRequest;
    }

    /**
     * Prefer the property's own listing agent; otherwise round-robin across
     * active agents by whoever has the fewest open viewing requests.
     */
    protected function assignAgent(?Property $property): ?User
    {
        if ($property?->agent_id) {
            return User::find($property->agent_id);
        }

        $activeAgentUserIds = \App\Models\Agent::where('status', 'active')->pluck('user_id');
        if ($activeAgentUserIds->isEmpty()) {
            return null;
        }

        $leastBusyUserId = ViewingRequest::whereIn('agent_id', $activeAgentUserIds)
            ->where('status', 'pending')
            ->selectRaw('agent_id, count(*) as open_count')
            ->groupBy('agent_id')
            ->orderBy('open_count')
            ->value('agent_id');

        $userId = $leastBusyUserId ?? $activeAgentUserIds->first();

        return User::find($userId);
    }
}
