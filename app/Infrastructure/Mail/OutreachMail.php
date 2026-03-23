<?php

namespace App\Infrastructure\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Support\Facades\URL;

class OutreachMail extends Mailable
{
    public string $unsubscribeUrl;

    public function __construct(
        public string $businessName,
        public int $reviewCount,
        public float $rating,
        public string $emailBody,
        public string $emailSubject,
        public string $senderName,
        public string $recipientEmail = '',
    ) {
        $this->unsubscribeUrl = URL::signedRoute('outreach.unsubscribe', ['email' => $this->recipientEmail]);
    }

    public function headers(): Headers
    {
        return new Headers(
            text: [
                'List-Unsubscribe' => "<{$this->unsubscribeUrl}>",
                'List-Unsubscribe-Post' => 'List-Unsubscribe=One-Click',
            ],
        );
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->emailSubject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.outreach',
            text: 'emails.outreach-text',
        );
    }
}
