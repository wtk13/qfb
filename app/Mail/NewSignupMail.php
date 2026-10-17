<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class NewSignupMail extends Mailable
{
    public function __construct(public readonly User $user) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: "New QuickFeedback signup: {$this->user->name}");
    }

    public function content(): Content
    {
        return new Content(text: 'mail.new-signup-text');
    }
}
