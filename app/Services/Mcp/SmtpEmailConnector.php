<?php

namespace App\Services\Mcp;

use App\Mail\ViewingRequestConfirmation;
use App\Models\ViewingRequest;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SmtpEmailConnector implements McpConnectorInterface
{
    public function getName(): string
    {
        return 'smtp_email';
    }

    public function isEnabled(): bool
    {
        return !empty(config('mail.mailers.smtp.host')) || config('mail.default') === 'log';
    }

    public function send(string $action, array $payload): array
    {
        return match ($action) {
            'viewing_confirmation' => $this->sendViewingConfirmation($payload),
            default => ['success' => false, 'message' => "Unsupported action for smtp_email: {$action}"],
        };
    }

    protected function sendViewingConfirmation(array $payload): array
    {
        /** @var ViewingRequest $viewingRequest */
        $viewingRequest = $payload['viewing_request'];
        $email = $payload['email'] ?? null;

        if (!$email) {
            return ['success' => false, 'message' => 'No email address on file for this lead.'];
        }

        try {
            Mail::to($email)->send(new ViewingRequestConfirmation($viewingRequest));
            return ['success' => true];
        } catch (Throwable $e) {
            report($e);
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
