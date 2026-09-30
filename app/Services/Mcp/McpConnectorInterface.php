<?php

namespace App\Services\Mcp;

/**
 * Standard contract for connecting HomeCyp's workflow automation to external
 * services (CRM, calendar, email, SMS...) — spec section 11. Only one real
 * connector ships in the MVP (SmtpEmailConnector), but every future integration
 * (HubSpot, Google Calendar, Twilio...) implements this same interface so the
 * automation layer never needs to change when a new connector is added.
 */
interface McpConnectorInterface
{
    public function getName(): string;

    public function isEnabled(): bool;

    /**
     * @param string $action e.g. 'viewing_confirmation', 'create_task', 'sync_lead'
     * @param array $payload action-specific data
     * @return array{success: bool, message?: string, data?: array}
     */
    public function send(string $action, array $payload): array;
}
