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
        {--sender-name=Mike : Your name}
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
        if (!$lock->get()) {
            $this->warn('Another outreach:daily-send is already running. Skipping.');
            return self::SUCCESS;
        }

        // Check daily cap (Resend free = 100/day, keep 20 buffer for transactional)
        $sentToday = OutreachLeadModel::whereNotIn('outreach_status', ['new', 'bounced'])
            ->whereDate('sent_at', today())
            ->count();

        $dailyCap = 80;
        $remaining = max(0, $dailyCap - $sentToday);
        $limit = min($limit, $remaining);

        if ($limit === 0) {
            $this->info("Daily cap reached ({$sentToday} sent today). Skipping.");
            $lock->release();
            return self::SUCCESS;
        }

        // Check monthly cap (Resend free = 3,000/month)
        $sentThisMonth = OutreachLeadModel::whereNotIn('outreach_status', ['new', 'bounced'])
            ->whereMonth('sent_at', now()->month)
            ->whereYear('sent_at', now()->year)
            ->count();

        $monthlyCap = 2500; // 3,000 actual, keep 500 buffer for transactional
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
            $subject = $this->generateSubject($lead->business_name, $lead->reviews);
            $body = $this->generateBody($lead, $senderName, $senderTitle);

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
                $this->error('         Failed: ' . $e->getMessage());

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
        $leads->pluck('category')->combine($leads->pluck('city'))
            ->unique()
            ->each(fn ($city, $category) => OutreachCampaignModel::refreshStats($category, $city));

        $totalQueue = OutreachLeadModel::sendable()->count();
        $this->info("Remaining in queue: {$totalQueue}");

        $lock->release();

        return self::SUCCESS;
    }

    private function generateSubject(string $businessName, int $reviews): string
    {
        return match (true) {
            $reviews === 0 => "{$businessName}'s Google profile — one thing I noticed",
            $reviews < 10 => "{$businessName} — quick thought on your Google reviews",
            default => "Idea for {$businessName} to get more 5-star reviews",
        };
    }

    private function generateBody(OutreachLeadModel $lead, string $senderName, string $senderTitle): string
    {
        $competitorPhrase = $this->buildCompetitorPhrase($lead->category, $lead->city);

        $reviewLine = match (true) {
            $lead->reviews === 0 => "I came across {$lead->business_name} on Google and noticed you don't have any reviews yet. That's actually a huge opportunity — most customers check reviews before calling, so even a handful puts you ahead of the pack.",
            $lead->reviews < 10 => "I came across {$lead->business_name} on Google Maps — {$lead->reviews} reviews at {$lead->rating} stars. Looking at {$competitorPhrase}, most have 30-50+. The gap is almost never about service quality. It's that most customers need a nudge.",
            default => "I was looking at {$lead->business_name}'s Google profile — {$lead->reviews} reviews at {$lead->rating} stars is solid. But {$competitorPhrase} have significantly more, which pushes them higher in local search results.",
        };

        return <<<EMAIL
{$reviewLine}

I made a free tool that generates a direct Google review link for your business. Your customers tap it and they're immediately writing a review — no searching, no extra steps.

Here's yours (free, no signup needed):
https://quickfeedback.app/tools/google-review-link-generator

Give it a try and let me know if it helps.

{$senderName}
{$senderTitle}
https://quickfeedback.app
EMAIL;
    }

    private function buildCompetitorPhrase(?string $category, ?string $city): string
    {
        $plural = match (strtolower($category ?? '')) {
            'dentist' => 'dentists',
            'plumber' => 'plumbers',
            'restaurant' => 'restaurants',
            'salon' => 'salons',
            'chiropractor' => 'chiropractors',
            'veterinarian' => 'vets',
            'gym' => 'gyms',
            'auto repair' => 'auto shops',
            'electrician' => 'electricians',
            'cleaning company' => 'cleaning companies',
            'accountant' => 'accountants',
            'real estate' => 'realtors',
            'landscaper' => 'landscapers',
            'roofer' => 'roofers',
            'hvac' => 'HVAC companies',
            default => 'similar businesses',
        };

        if ($category && $city) {
            return "other {$plural} in {$city}";
        }

        return "your competitors in the area";
    }
}
