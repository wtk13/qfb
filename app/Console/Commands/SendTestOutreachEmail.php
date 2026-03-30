<?php

namespace App\Console\Commands;

use App\Infrastructure\Mail\OutreachMail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendTestOutreachEmail extends Command
{
    protected $signature = 'outreach:test
        {email : Your email address to send the test to}
        {--sender-name=Wojtek : Your name for the email footer}';

    protected $description = 'Send a test outreach email to yourself to verify everything works on prod';

    public function handle(): int
    {
        $email = $this->argument('email');
        $senderName = $this->option('sender-name');

        $businessName = 'Austin Family Dental';
        $reviews = 12;
        $rating = 4.2;
        $subject = 'Idea for Austin Family Dental to get more 5-star reviews';

        $body = <<<EMAIL
I was looking at Austin Family Dental's Google profile — 12 reviews at 4.2 stars is solid. But other dentists in Austin have significantly more, which pushes them higher in local search results.

I made a free tool that generates a direct Google review link for your business. Your customers tap it and they're immediately writing a review — no searching, no extra steps.

Here's yours (free, no signup needed):
https://quickfeedback.app/tools/google-review-link-generator

Give it a try and let me know if it helps.

{$senderName}
Founder, QuickFeedback
https://quickfeedback.app
EMAIL;

        $this->info("Sending test outreach email to: {$email}");

        try {
            Mail::mailer('resend')
                ->to($email)
                ->send(new OutreachMail(
                    businessName: $businessName,
                    reviewCount: $reviews,
                    rating: $rating,
                    emailBody: $body,
                    emailSubject: $subject,
                    senderName: $senderName,
                    recipientEmail: $email,
                ));

            $this->info('Test email sent! Check your inbox.');
        } catch (\Exception $e) {
            $this->error('Failed: '.$e->getMessage());

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
