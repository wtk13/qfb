<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Support\Str;

class TrialEndingMail extends Mailable
{
    public function __construct(
        public readonly User $user,
        public readonly \DateTimeInterface $trialEndsAt,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            replyTo: [new Address(config('company.support_email'), 'Wojtek from QuickFeedback')],
            subject: 'Your QuickFeedback trial ends on '.$this->trialEndsAt->format('l, M j'),
        );
    }

    public function content(): Content
    {
        return new Content(
            text: 'mail.trial-ending-text',
            with: [
                'firstName' => Str::before(trim($this->user->name), ' '),
                'endsOn' => $this->trialEndsAt->format('l, F j'),
            ],
        );
    }
}
