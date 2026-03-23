<?php

namespace App\Console\Commands;

use App\Infrastructure\Persistence\Eloquent\OutreachCampaignModel;
use App\Infrastructure\Persistence\Eloquent\OutreachLeadModel;
use Illuminate\Console\Command;

class OutreachStats extends Command
{
    protected $signature = 'outreach:stats
        {--mark-replied= : Mark a lead as replied by email address}
        {--mark-converted= : Mark a lead as converted by email address}
        {--mark-unsubscribed= : Mark a lead as unsubscribed by email address}';

    protected $description = 'Show outreach pipeline stats and manually track replies/conversions';

    public function handle(): int
    {
        // Handle manual status updates
        if ($email = $this->option('mark-replied')) {
            return $this->markLead($email, 'replied');
        }
        if ($email = $this->option('mark-converted')) {
            return $this->markLead($email, 'converted');
        }
        if ($email = $this->option('mark-unsubscribed')) {
            return $this->markLead($email, 'unsubscribed');
        }

        // Show stats
        $this->showPipelineOverview();
        $this->showCampaignBreakdown();
        $this->showRecentActivity();
        $this->showResendBudget();

        return self::SUCCESS;
    }

    private function markLead(string $email, string $status): int
    {
        $lead = OutreachLeadModel::where('email', $email)->first();

        if (!$lead) {
            $this->error("No lead found with email: {$email}");
            return self::FAILURE;
        }

        $lead->update(['outreach_status' => $status]);

        // Refresh campaign stats from source of truth
        OutreachCampaignModel::refreshStats($lead->category, $lead->city);

        $this->info("Marked {$lead->business_name} ({$email}) as: {$status}");
        return self::SUCCESS;
    }

    private function showPipelineOverview(): void
    {
        $this->info('=== Outreach Pipeline Overview ===');
        $this->newLine();

        $total = OutreachLeadModel::count();
        $verified = OutreachLeadModel::where('email_status', 'verified')->count();
        $queue = OutreachLeadModel::sendable()->count();
        $sent = OutreachLeadModel::where('outreach_status', 'sent')->count();
        $replied = OutreachLeadModel::where('outreach_status', 'replied')->count();
        $converted = OutreachLeadModel::where('outreach_status', 'converted')->count();
        $bounced = OutreachLeadModel::where('outreach_status', 'bounced')->count();
        $unsubscribed = OutreachLeadModel::where('outreach_status', 'unsubscribed')->count();

        $this->table(
            ['Metric', 'Count'],
            [
                ['Total leads scraped', $total],
                ['Emails verified', $verified],
                ['Ready to send', $queue],
                ['Sent', $sent],
                ['Replied', $replied],
                ['Converted (signed up)', $converted],
                ['Bounced', $bounced],
                ['Unsubscribed', $unsubscribed],
                ['Reply rate', $sent > 0 ? round(($replied / $sent) * 100, 1) . '%' : '-'],
                ['Conversion rate', $sent > 0 ? round(($converted / $sent) * 100, 1) . '%' : '-'],
            ]
        );
    }

    private function showCampaignBreakdown(): void
    {
        $campaigns = OutreachCampaignModel::orderByDesc('emails_sent')->get();

        if ($campaigns->isEmpty()) {
            return;
        }

        $this->newLine();
        $this->info('=== Campaign Breakdown ===');
        $this->newLine();

        $this->table(
            ['Category', 'City', 'Scraped', 'Verified', 'Sent', 'Replies', 'Conv.', 'Reply %', 'Last Scraped'],
            $campaigns->map(fn ($c) => [
                $c->category,
                $c->city,
                $c->leads_scraped,
                $c->emails_verified,
                $c->emails_sent,
                $c->replies,
                $c->conversions,
                $c->emails_sent > 0 ? round(($c->replies / $c->emails_sent) * 100, 1) . '%' : '-',
                $c->scraped_at?->diffForHumans() ?? 'never',
            ])->toArray()
        );
    }

    private function showRecentActivity(): void
    {
        $recent = OutreachLeadModel::where('outreach_status', 'sent')
            ->orderByDesc('sent_at')
            ->limit(5)
            ->get();

        if ($recent->isEmpty()) {
            return;
        }

        $this->newLine();
        $this->info('=== Last 5 Emails Sent ===');
        $this->newLine();

        $this->table(
            ['Business', 'Email', 'Category', 'City', 'Sent'],
            $recent->map(fn ($l) => [
                substr($l->business_name, 0, 30),
                $l->email,
                $l->category,
                $l->city,
                $l->sent_at?->diffForHumans() ?? '-',
            ])->toArray()
        );
    }

    private function showResendBudget(): void
    {
        $this->newLine();
        $this->info('=== Resend Free Tier Budget ===');

        $sentThisMonth = OutreachLeadModel::where('outreach_status', 'sent')
            ->whereMonth('sent_at', now()->month)
            ->whereYear('sent_at', now()->year)
            ->count();

        $sentToday = OutreachLeadModel::where('outreach_status', 'sent')
            ->whereDate('sent_at', today())
            ->count();

        $monthlyLimit = 3000;
        $dailyLimit = 80; // 100 actual, 20 buffer

        $this->table(
            ['Period', 'Sent', 'Limit', 'Remaining'],
            [
                ['Today', $sentToday, $dailyLimit, max(0, $dailyLimit - $sentToday)],
                ['This month', $sentThisMonth, $monthlyLimit, max(0, $monthlyLimit - $sentThisMonth)],
            ]
        );
    }
}
