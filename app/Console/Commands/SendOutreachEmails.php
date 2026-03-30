<?php

namespace App\Console\Commands;

use App\Infrastructure\Mail\OutreachMail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class SendOutreachEmails extends Command
{
    protected $signature = 'outreach:send
        {--input=outreach.csv : Input CSV file in storage/app/}
        {--limit=10 : Max emails to send in this run}
        {--delay=30 : Seconds between emails}
        {--sender-name=Wojtek : Your name for the email footer}
        {--dry-run : Preview emails without sending}';

    protected $description = 'Send personalized outreach emails from generated CSV via Resend';

    public function handle(): int
    {
        $inputFile = $this->option('input');
        $limit = (int) $this->option('limit');
        $delay = (int) $this->option('delay');
        $senderName = $this->option('sender-name');
        $dryRun = $this->option('dry-run');

        if (! Storage::disk('local')->exists($inputFile)) {
            $this->error("File not found: storage/app/{$inputFile}");
            $this->info('Run outreach:generate first.');

            return self::FAILURE;
        }

        $handle = fopen(Storage::disk('local')->path($inputFile), 'r');
        $header = fgetcsv($handle);
        $leads = [];
        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) === count($header)) {
                $leads[] = array_combine($header, $row);
            }
        }
        fclose($handle);

        // Filter out leads without email
        $leads = array_filter($leads, fn ($l) => ! empty($l['Email']));
        $leads = array_values($leads);

        if (empty($leads)) {
            $this->warn('No leads with email addresses found.');

            return self::SUCCESS;
        }

        // Load sent log to skip already-sent emails
        $sentFile = 'outreach_sent.log';
        $sentEmails = [];
        if (Storage::disk('local')->exists($sentFile)) {
            $sentEmails = array_filter(explode("\n", Storage::disk('local')->get($sentFile)));
        }

        // Load unsubscribe log to respect opt-outs
        $unsubFile = 'outreach_unsubscribed.log';
        $unsubEmails = [];
        if (Storage::disk('local')->exists($unsubFile)) {
            $unsubEmails = array_filter(explode("\n", Storage::disk('local')->get($unsubFile)));
        }

        $skippedEmails = array_unique(array_merge($sentEmails, $unsubEmails));
        $leads = array_filter($leads, fn ($l) => ! in_array($l['Email'], $skippedEmails));
        $leads = array_values($leads);

        if (empty($leads)) {
            $this->info('All leads in this file have already been emailed.');

            return self::SUCCESS;
        }

        $toSend = array_slice($leads, 0, $limit);

        $this->info(sprintf(
            '%s %d emails (of %d remaining) with %ds delay...',
            $dryRun ? 'DRY RUN — previewing' : 'Sending',
            count($toSend),
            count($leads),
            $delay
        ));

        $this->newLine();
        $sent = 0;
        $failed = 0;

        foreach ($toSend as $i => $lead) {
            $this->line(sprintf(
                '  [%d/%d] %s → %s',
                $i + 1,
                count($toSend),
                $lead['Business Name'],
                $lead['Email']
            ));
            $this->line(sprintf('         Subject: %s', $lead['Subject']));

            if ($dryRun) {
                $this->info('         (dry run — skipped)');
                $sent++;

                continue;
            }

            try {
                Mail::mailer('resend')
                    ->to($lead['Email'])
                    ->send(new OutreachMail(
                        businessName: $lead['Business Name'],
                        reviewCount: (int) $lead['Reviews'],
                        rating: (float) $lead['Rating'],
                        emailBody: $lead['Body'],
                        emailSubject: $lead['Subject'],
                        senderName: $senderName,
                        recipientEmail: $lead['Email'],
                    ));

                // Log sent email
                Storage::disk('local')->append($sentFile, $lead['Email']);

                $sent++;
                $this->info('         Sent!');
            } catch (\Exception $e) {
                $failed++;
                $this->error('         Failed: '.$e->getMessage());
            }

            // Delay between sends (except after last one)
            if ($i < count($toSend) - 1) {
                sleep($delay);
            }
        }

        $this->newLine();
        $this->info("Done. Sent: {$sent}, Failed: {$failed}");

        if (! $dryRun && count($leads) > $limit) {
            $remaining = count($leads) - $limit;
            $this->newLine();
            $this->info("Run again to send the next batch ({$remaining} remaining).");
        }

        return self::SUCCESS;
    }
}
