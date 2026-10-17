<?php

namespace App\Console\Commands;

use App\Infrastructure\Mail\OutreachMail;
use App\Infrastructure\Persistence\Eloquent\OutreachCampaignModel;
use App\Infrastructure\Persistence\Eloquent\OutreachLeadModel;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

class OutreachDailySend extends Command
{
    protected $signature = 'outreach:daily-send
        {--limit=15 : Max emails to send today (Resend free = 100/day, stay conservative)}
        {--delay=30 : Seconds between emails}
        {--sender-name=Wojtek : Your name}
        {--sender-title=Founder, QuickFeedback : Your title}
        {--dry-run : Preview without sending}';

    protected $description = 'Daily automated outreach: send emails to verified leads from the pipeline';

    public function handle(): int
    {
        $limit = (int) $this->option('limit');
        $delay = (int) $this->option('delay');
        $senderName = $this->option('sender-name');
        $senderTitle = $this->option('sender-title');
        $dryRun = $this->option('dry-run');

        // Prevent concurrent runs
        $lock = Cache::lock('outreach-daily-send', 600);
        if (! $lock->get()) {
            $this->warn('Another outreach:daily-send is already running. Skipping.');

            return self::SUCCESS;
        }

        // Check daily cap (Resend free = 100/day, keep 20 buffer for transactional)
        $sentToday = OutreachLeadModel::whereNotNull('sent_at')
            ->whereDate('sent_at', today())
            ->count();

        $dailyCap = config('outreach.daily_cap');
        $remaining = max(0, $dailyCap - $sentToday);
        $limit = min($limit, $remaining);

        if ($limit === 0) {
            $this->info("Daily cap reached ({$sentToday} sent today). Skipping.");
            $lock->release();

            return self::SUCCESS;
        }

        // Check monthly cap (Resend free = 3,000/month, keep 500 buffer for transactional)
        $sentThisMonth = OutreachLeadModel::whereNotNull('sent_at')
            ->whereMonth('sent_at', now()->month)
            ->whereYear('sent_at', now()->year)
            ->count();

        $monthlyCap = config('outreach.monthly_cap');
        $monthlyRemaining = max(0, $monthlyCap - $sentThisMonth);
        $limit = min($limit, $monthlyRemaining);

        if ($limit === 0) {
            $this->info("Monthly cap reached ({$sentThisMonth} sent this month). Skipping.");
            $lock->release();

            return self::SUCCESS;
        }

        // Get sendable leads, oldest first, spread across categories
        $leads = OutreachLeadModel::sendable()
            ->orderBy('created_at')
            ->limit($limit)
            ->get();

        if ($leads->isEmpty()) {
            $this->info('No verified leads to send. Run outreach:weekly to scrape more.');

            return self::SUCCESS;
        }

        $this->info(sprintf(
            '%s %d emails (%d sent today, %d daily cap)...',
            $dryRun ? 'DRY RUN — previewing' : 'Sending',
            $leads->count(),
            $sentToday,
            $dailyCap
        ));
        $this->newLine();

        $sent = 0;
        $failed = 0;

        foreach ($leads as $i => $lead) {
            $competitors = $this->competitorsFor($lead);
            $subject = $this->generateSubject($lead, $competitors);
            $body = $this->generateBody($lead, $competitors, $senderName, $senderTitle);

            $this->line(sprintf(
                '  [%d/%d] %s → %s',
                $i + 1,
                $leads->count(),
                $lead->business_name,
                $lead->email
            ));

            if ($dryRun) {
                $this->info("         Subject: {$subject}");
                $this->info('         (dry run — skipped)');
                $sent++;

                continue;
            }

            try {
                Mail::mailer('resend')
                    ->to($lead->email)
                    ->send(new OutreachMail(
                        businessName: $lead->business_name,
                        reviewCount: $lead->reviews,
                        rating: $lead->rating,
                        emailBody: $body,
                        emailSubject: $subject,
                        senderName: $senderName,
                        recipientEmail: $lead->email,
                    ));

                $lead->update([
                    'outreach_status' => 'sent',
                    'sent_at' => now(),
                ]);

                $sent++;
                $this->info('         Sent!');
            } catch (\Exception $e) {
                $failed++;
                $this->error('         Failed: '.$e->getMessage());

                // Mark as bounced if it's an email delivery error
                if (str_contains($e->getMessage(), 'bounce') || str_contains($e->getMessage(), 'rejected')) {
                    $lead->update(['outreach_status' => 'bounced']);
                }
            }

            if ($i < $leads->count() - 1) {
                sleep($delay);
            }
        }

        $this->newLine();
        $this->info("Done. Sent: {$sent}, Failed: {$failed}");

        // Refresh campaign stats from source of truth
        $leads->unique(fn ($l) => $l->category.'|'.$l->city)
            ->each(fn ($lead) => OutreachCampaignModel::refreshStats($lead->category, $lead->city));

        $totalQueue = OutreachLeadModel::sendable()->count();
        $this->info("Remaining in queue: {$totalQueue}");

        $lock->release();

        return self::SUCCESS;
    }

    /**
     * Review counts of the top Google results for the lead's niche, or [] when there is
     * nothing honest to compare with (no data yet, or the lead already ranks in the top 3).
     *
     * @return int[]
     */
    private function competitorsFor(OutreachLeadModel $lead): array
    {
        $top = OutreachCampaignModel::where('category', $lead->category)
            ->where('city', $lead->city)
            ->value('top_competitors');

        $top = is_string($top) ? json_decode($top, true) : $top;

        if (empty($top) || in_array($lead->place_id, array_column($top, 'place_id'), true)) {
            return [];
        }

        $counts = array_column($top, 'reviews');

        return count($counts) >= 2 ? $counts : [];
    }

    private function generateSubject(OutreachLeadModel $lead, array $competitors): string
    {
        if ($competitors) {
            return sprintf('Google reviews: %s vs. the top %d in %s', $lead->business_name, count($competitors), $lead->city);
        }

        return "Quick question about {$lead->business_name}'s Google reviews";
    }

    private function generateBody(OutreachLeadModel $lead, array $competitors, string $senderName, string $senderTitle): string
    {
        $opening = match (true) {
            (bool) $competitors => sprintf(
                'I searched "%s in %s" on Google. The top %d results have %s reviews. %s has %s.',
                $lead->category,
                $lead->city,
                count($competitors),
                $this->joinCounts($competitors),
                $lead->business_name,
                number_format($lead->reviews),
            ),
            $lead->reviews === 0 => "I came across {$lead->business_name} on Google Maps and noticed you don't have any reviews yet.",
            default => "I came across {$lead->business_name} on Google Maps: {$lead->reviews} reviews at {$lead->rating} stars.",
        };

        $why = $competitors
            ? 'That gap is rarely about the service.'
            : 'Getting more reviews is rarely about the service.';

        $landingUrl = route('outreach.landing', ['placeId' => $lead->place_id]);

        return <<<EMAIL
Hi,

{$opening}

{$why} Happy customers just don't think to leave a review unless someone asks at the right moment.

I built QuickFeedback to do the asking for you: after each job your customer gets a short rating request by email or QR code, happy customers land right on your Google review page, and you hear privately about anyone who had a bad experience.

Here's a quick snapshot for {$lead->business_name}, with your direct Google review link:

{$landingUrl}

If you want, I'll set it up for you for free this week so you can see if it works. Interested?

{$senderName}
{$senderTitle}
EMAIL;
    }

    /** @param int[] $counts */
    private function joinCounts(array $counts): string
    {
        $formatted = array_map(fn (int $n) => number_format($n), $counts);
        $last = array_pop($formatted);

        return implode(', ', $formatted).' and '.$last;
    }
}
