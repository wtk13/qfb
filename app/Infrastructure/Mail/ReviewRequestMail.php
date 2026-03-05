<?php

namespace App\Infrastructure\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReviewRequestMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $businessName,
        public string $ratingUrl,
        public ?string $logoPath = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('campaign.email_subject', ['business' => $this->businessName]),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.review-request',
        );
    }
}
