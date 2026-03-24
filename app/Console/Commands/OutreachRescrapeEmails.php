<?php

namespace App\Console\Commands;

use App\Infrastructure\Persistence\Eloquent\OutreachLeadModel;
use App\Infrastructure\Scraper\WebsiteEmailScraper;
use Illuminate\Console\Command;

class OutreachRescrapeEmails extends Command
{
    protected $signature = 'outreach:rescrape-emails
        {--status=* : Only rescrape leads with these email statuses}
        {--limit=0 : Max leads to process (0 = all)}
        {--dry-run : Preview without updating}';

    protected $description = 'Re-scrape business websites to replace info@ guesses with real email addresses';

    public function handle(WebsiteEmailScraper $scraper): int
    {
        $dryRun = $this->option('dry-run');
        $limit = (int) $this->option('limit');
        $statuses = $this->option('status');

        // Query info@ leads first (upgrade priority)
        $infoQuery = OutreachLeadModel::whereNotNull('website')
            ->where('website', '!=', '')
            ->whereIn('outreach_status', ['new', 'bounced'])
            ->whereNotNull('email')
            ->where('email', 'like', 'info@%');

        // Then leads with no email or invalid status
        $noEmailQuery = OutreachLeadModel::whereNotNull('website')
            ->where('website', '!=', '')
            ->whereIn('outreach_status', ['new', 'bounced'])
            ->where(fn ($q) => $q->whereNull('email')->orWhere('email_status', 'invalid'));

        if (!empty($statuses)) {
            $infoQuery->whereIn('email_status', $statuses);
            $noEmailQuery->whereIn('email_status', $statuses);
        }

        $infoLeads = $infoQuery->orderBy('created_at')->get();
        $noEmailLeads = $noEmailQuery->orderBy('created_at')->get();
        $allLeads = $infoLeads->merge($noEmailLeads)->unique('id');

        if ($limit > 0) {
            $allLeads = $allLeads->take($limit);
        }

        $total = $allLeads->count();

        if ($total === 0) {
            $this->info('No leads to rescrape.');
            return self::SUCCESS;
        }

        $this->info(sprintf(
            '%s %d leads (%d info@ emails, %d missing/invalid emails)',
            $dryRun ? 'DRY RUN — previewing' : 'Rescraping',
            $total,
            $infoLeads->count(),
            $noEmailLeads->count(),
        ));
        $this->newLine();

        $upgraded = 0;
        $found = 0;
        $unchanged = 0;

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        foreach ($allLeads as $lead) {
            $bar->advance();

            try {
                $newEmail = $scraper->scrape($lead->website);
            } catch (\Exception $e) {
                $unchanged++;
                continue;
            }

            if (!$newEmail || $newEmail === $lead->email) {
                $unchanged++;
                continue;
            }

            $oldEmail = $lead->email ?: '(none)';
            $wasInfo = str_starts_with($oldEmail, 'info@');

            if ($dryRun) {
                $this->newLine();
                $this->line("  {$lead->business_name}: {$oldEmail} → {$newEmail}");
            } else {
                $lead->update([
                    'email' => $newEmail,
                    'email_status' => 'pending',
                ]);
            }

            $wasInfo ? $upgraded++ : $found++;

            // Small delay to avoid hammering external websites
            usleep(200_000);
        }

        $bar->finish();
        $this->newLine(2);

        $this->info('=== Results ===');
        $this->info("Upgraded from info@: {$upgraded}");
        $this->info("Found new email:     {$found}");
        $this->info("Unchanged:           {$unchanged}");

        if (!$dryRun && ($upgraded + $found) > 0) {
            $this->newLine();
            $this->info('Run outreach:weekly to verify the new emails via MX lookup.');
        }

        return self::SUCCESS;
    }
}
