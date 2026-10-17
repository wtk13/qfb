<?php

namespace App\Console\Commands;

use App\Application\Command\SendReviewReminders;
use Illuminate\Console\Command;

class SendReviewRemindersCommand extends Command
{
    protected $signature = 'reviews:send-reminders';

    protected $description = 'Send one reminder to customers who have not opened a review request after 3 days';

    public function handle(SendReviewReminders $sendReviewReminders): int
    {
        $sent = $sendReviewReminders->execute(new \DateTimeImmutable);

        $this->info("Queued {$sent} review request reminders.");

        return self::SUCCESS;
    }
}
