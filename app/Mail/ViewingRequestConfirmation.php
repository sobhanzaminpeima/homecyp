<?php

namespace App\Mail;

use App\Models\ViewingRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ViewingRequestConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public ViewingRequest $viewingRequest)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Your viewing request has been received — HomeCyp');
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.viewing-request-confirmation',
            with: [
                'viewingRequest' => $this->viewingRequest,
                'property' => $this->viewingRequest->property,
                'lead' => $this->viewingRequest->lead,
            ],
        );
    }
}
