<?php

namespace App\Services\Mcp;

use App\Models\Lead;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CrmWebhookConnector
{
    public function sync(Lead $lead): void
    {
        $url = config('services.crm.webhook_url');
        if (!$url) {
            return;
        }

        try {
            Http::withToken((string) config('services.crm.webhook_token'))
                ->timeout(5)->post($url, [
                    'event' => 'lead.created',
                    'lead' => $lead->only(['id', 'name', 'email', 'phone', 'country', 'type', 'status', 'source', 'preferred_language', 'meta_data']),
                ])->throw();
        } catch (\Throwable $e) {
            Log::warning('CRM lead sync failed', ['lead_id' => $lead->id, 'error' => $e->getMessage()]);
        }
    }
}
