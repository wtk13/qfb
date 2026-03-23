<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class OutreachCampaignModel extends Model
{
    use HasUuids;

    protected $table = 'outreach_campaigns';

    protected $fillable = [
        'category',
        'city',
        'leads_scraped',
        'emails_found',
        'emails_verified',
        'emails_sent',
        'replies',
        'conversions',
        'scraped_at',
        'last_sent_at',
        'new_leads_last_scrape',
    ];

    protected function casts(): array
    {
        return [
            'scraped_at' => 'datetime',
            'last_sent_at' => 'datetime',
            'new_leads_last_scrape' => 'integer',
        ];
    }

    /**
     * Recompute stats from the leads table (source of truth).
     */
    public static function refreshStats(string $category, string $city): void
    {
        $base = OutreachLeadModel::where('category', $category)->where('city', $city);

        static::updateOrCreate(
            ['category' => $category, 'city' => $city],
            [
                'leads_scraped' => (clone $base)->count(),
                'emails_found' => (clone $base)->whereNotNull('email')->count(),
                'emails_verified' => (clone $base)->where('email_status', 'verified')->count(),
                'emails_sent' => (clone $base)->where('outreach_status', '!=', 'new')->where('outreach_status', '!=', 'bounced')->whereNotNull('sent_at')->count(),
                'replies' => (clone $base)->where('outreach_status', 'replied')->count(),
                'conversions' => (clone $base)->where('outreach_status', 'converted')->count(),
            ],
        );
    }
}
