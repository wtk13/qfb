<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Support\Str;

class WelcomeMail extends Mailable
{
    public function __construct(public readonly User $user) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            replyTo: [new Address(config('company.support_email'), 'Wojtek from QuickFeedback')],
            subject: 'Welcome to QuickFeedback - want me to set it up for you?',
        );
    }

    public function content(): Content
    {
        return new Content(
            text: 'mail.welcome-text',
            with: ['firstName' => Str::before(trim($this->user->name), ' ')],
        );
    }
}
