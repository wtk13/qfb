<?php

namespace App\Mail;

use App\Infrastructure\Persistence\Eloquent\FeedbackTriageModel;
use Illuminate\Mail\Mailable;

class NegativeFeedbackMail extends Mailable
{
    public function __construct(
        private readonly string $businessName,
        private readonly string $comment,
        private readonly ?FeedbackTriageModel $triage,
    ) {}

    public function build(): self
    {
        return $this
            ->subject(__('feedback.notification_subject', ['business' => $this->businessName]))
            ->view('mail.negative-feedback', [
                'businessName' => $this->businessName,
                'comment' => $this->comment,
                'triage' => $this->triage,
            ]);
    }
}
