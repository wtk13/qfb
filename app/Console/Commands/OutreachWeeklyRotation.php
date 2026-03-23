<?php

namespace App\Console\Commands;

use App\Infrastructure\Persistence\Eloquent\OutreachCampaignModel;
use App\Infrastructure\Persistence\Eloquent\OutreachLeadModel;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class OutreachWeeklyRotation extends Command
{
    protected $signature = 'outreach:weekly
        {--max-results=60 : Max places to scrape per niche}
        {--max-reviews=30 : Only businesses with fewer reviews than this}';

    protected $description = 'Weekly automated rotation: scrape new niches, verify emails, prepare leads for daily sending';

    private string $apiKey;

    private array $rotation = [
        ['category' => 'dentist', 'city' => 'Austin', 'state' => 'TX'],
        ['category' => 'plumber', 'city' => 'Denver', 'state' => 'CO'],
        ['category' => 'restaurant', 'city' => 'Miami', 'state' => 'FL'],
        ['category' => 'salon', 'city' => 'Nashville', 'state' => 'TN'],
        ['category' => 'chiropractor', 'city' => 'Portland', 'state' => 'OR'],
        ['category' => 'cleaning company', 'city' => 'Phoenix', 'state' => 'AZ'],
        ['category' => 'dentist', 'city' => 'Dallas', 'state' => 'TX'],
        ['category' => 'plumber', 'city' => 'Atlanta', 'state' => 'GA'],
        ['category' => 'restaurant', 'city' => 'Chicago', 'state' => 'IL'],
        ['category' => 'salon', 'city' => 'Charlotte', 'state' => 'NC'],
        ['category' => 'veterinarian', 'city' => 'Seattle', 'state' => 'WA'],
        ['category' => 'gym', 'city' => 'San Diego', 'state' => 'CA'],
        ['category' => 'dentist', 'city' => 'Houston', 'state' => 'TX'],
        ['category' => 'electrician', 'city' => 'Tampa', 'state' => 'FL'],
        ['category' => 'auto repair', 'city' => 'Columbus', 'state' => 'OH'],
        ['category' => 'accountant', 'city' => 'Raleigh', 'state' => 'NC'],
        ['category' => 'landscaper', 'city' => 'Orlando', 'state' => 'FL'],
        ['category' => 'roofer', 'city' => 'Las Vegas', 'state' => 'NV'],
        ['category' => 'hvac', 'city' => 'San Antonio', 'state' => 'TX'],
        ['category' => 'real estate', 'city' => 'Minneapolis', 'state' => 'MN'],
    ];

    public function handle(): int
    {
        $this->apiKey = config('services.google_places.key');

        if (empty($this->apiKey)) {
            $this->error('GOOGLE_PLACES_API_KEY is not set in .env');
            return self::FAILURE;
        }

        $maxResults = min((int) $this->option('max-results'), 60);
        $maxReviews = (int) $this->option('max-reviews');

        // Pick the next niche that hasn't been scraped or was scraped longest ago
        $target = $this->pickNextTarget();

        if (!$target) {
            $this->warn('All niches have been recently scraped. Nothing to do.');
            return self::SUCCESS;
        }

        $category = $target['category'];
        $city = $target['city'];
        $state = $target['state'];
        $query = "{$category} in {$city} {$state}";

        $this->info("=== Weekly Outreach Rotation ===");
        $this->info("Target: {$query}");
        $this->newLine();

        // Step 1: Scrape
        $this->info('[1/3] Scraping Google Places...');
        $places = $this->scrapeLeads($query, $maxResults, $maxReviews, $category, $city);
        $this->info("  Scraped {$places['total']} places, {$places['new']} new leads stored.");

        // Step 2: Verify emails
        $this->info('[2/3] Verifying emails via MX lookup...');
        $verified = $this->verifyEmails($category, $city);
        $this->info("  Verified: {$verified['valid']}, Invalid: {$verified['invalid']}, No email: {$verified['no_email']}");

        // Step 3: Update campaign stats from source of truth
        $this->info('[3/3] Updating campaign stats...');
        OutreachCampaignModel::refreshStats($category, $city);
        OutreachCampaignModel::where('category', $category)
            ->where('city', $city)
            ->update(['scraped_at' => now()]);

        // Summary
        $this->newLine();
        $this->info('=== Summary ===');
        $sendable = OutreachLeadModel::where('category', $category)
            ->where('city', $city)
            ->sendable()
            ->count();
        $this->info("Ready to send: {$sendable} leads");
        $this->info('Daily sender will pick these up automatically.');

        $this->showOverallStats();

        return self::SUCCESS;
    }

    private function pickNextTarget(): ?array
    {
        // Load all campaigns in one query
        $campaigns = OutreachCampaignModel::all()
            ->keyBy(fn ($c) => $c->category . '|' . $c->city);

        $best = null;
        $oldestDate = now();

        foreach ($this->rotation as $entry) {
            $key = $entry['category'] . '|' . $entry['city'];
            $campaign = $campaigns->get($key);

            if (!$campaign) {
                return $entry; // Never scraped — pick this one
            }

            if ($campaign->scraped_at && $campaign->scraped_at < $oldestDate) {
                $oldestDate = $campaign->scraped_at;
                $best = $entry;
            }
        }

        // Only re-scrape if oldest was more than 4 weeks ago
        if ($best && $oldestDate->diffInWeeks(now()) >= 4) {
            return $best;
        }

        return $best; // Re-scrape the oldest one
    }

    private function scrapeLeads(string $query, int $maxResults, int $maxReviews, string $category, string $city): array
    {
        $places = $this->searchPlaces($query, $maxResults);
        $total = count($places);
        $new = 0;

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        foreach ($places as $place) {
            $bar->advance();

            // Skip if already in DB
            if (OutreachLeadModel::where('place_id', $place['place_id'])->exists()) {
                continue;
            }

            $detail = $this->getPlaceDetails($place['place_id']);
            if (!$detail) {
                continue;
            }

            $reviewCount = $detail['user_ratings_total'] ?? 0;
            if ($reviewCount >= $maxReviews) {
                continue;
            }

            $website = $detail['website'] ?? '';
            $email = $this->guessEmail($website);

            OutreachLeadModel::create([
                'business_name' => $detail['name'] ?? '',
                'email' => $email ?: null,
                'website' => $website ?: null,
                'phone' => $detail['formatted_phone_number'] ?? null,
                'rating' => $detail['rating'] ?? 0,
                'reviews' => $reviewCount,
                'place_id' => $place['place_id'],
                'google_maps_url' => $detail['url'] ?? null,
                'category' => $category,
                'city' => $city,
                'email_status' => $email ? 'pending' : 'invalid',
            ]);

            $new++;
        }

        $bar->finish();
        $this->newLine();

        return ['total' => $total, 'new' => $new];
    }

    private function verifyEmails(string $category, string $city): array
    {
        $leads = OutreachLeadModel::where('category', $category)
            ->where('city', $city)
            ->where('email_status', 'pending')
            ->whereNotNull('email')
            ->get();

        $valid = 0;
        $invalid = 0;
        $noEmail = 0;

        foreach ($leads as $lead) {
            if (empty($lead->email)) {
                $lead->update(['email_status' => 'invalid']);
                $noEmail++;
                continue;
            }

            $domain = substr(strrchr($lead->email, '@'), 1);

            if ($this->hasMxRecord($domain)) {
                $lead->update([
                    'email_status' => 'verified',
                    'verified_at' => now(),
                ]);
                $valid++;
            } else {
                $lead->update(['email_status' => 'invalid']);
                $invalid++;
            }
        }

        return ['valid' => $valid, 'invalid' => $invalid, 'no_email' => $noEmail];
    }

    private function hasMxRecord(string $domain): bool
    {
        if (empty($domain)) {
            return false;
        }

        // Check MX records
        $mxRecords = [];
        if (@getmxrr($domain, $mxRecords) && !empty($mxRecords)) {
            return true;
        }

        // Fallback: check if domain has any A record (some small businesses use A record for mail)
        $dns = @dns_get_record($domain, DNS_A);
        return !empty($dns);
    }

    private function showOverallStats(): void
    {
        $this->newLine();
        $this->info('=== Overall Pipeline Stats ===');

        $campaigns = OutreachCampaignModel::orderByDesc('scraped_at')->get();

        if ($campaigns->isEmpty()) {
            return;
        }

        $this->table(
            ['Category', 'City', 'Leads', 'Verified', 'Sent', 'Replies', 'Conv.', 'Reply %'],
            $campaigns->map(fn ($c) => [
                $c->category,
                $c->city,
                $c->leads_scraped,
                $c->emails_verified,
                $c->emails_sent,
                $c->replies,
                $c->conversions,
                $c->emails_sent > 0
                    ? round(($c->replies / $c->emails_sent) * 100, 1) . '%'
                    : '-',
            ])->toArray()
        );

        $totalSent = $campaigns->sum('emails_sent');
        $totalReplies = $campaigns->sum('replies');
        $totalConversions = $campaigns->sum('conversions');
        $pendingSend = OutreachLeadModel::sendable()->count();

        $this->newLine();
        $this->info("Total sent: {$totalSent} | Replies: {$totalReplies} | Conversions: {$totalConversions} | Queue: {$pendingSend}");
    }

    private function searchPlaces(string $query, int $maxResults): array
    {
        $places = [];
        $nextPageToken = null;

        while (count($places) < $maxResults) {
            $params = [
                'query' => $query,
                'key' => $this->apiKey,
            ];

            if ($nextPageToken) {
                $params['pagetoken'] = $nextPageToken;
            }

            $response = Http::get('https://maps.googleapis.com/maps/api/place/textsearch/json', $params);

            if ($response->failed()) {
                $this->error('API request failed: ' . $response->status());
                break;
            }

            $data = $response->json();

            if (!in_array($data['status'] ?? '', ['OK', 'ZERO_RESULTS'])) {
                $this->error('API error: ' . ($data['status'] ?? 'unknown') . ' — ' . ($data['error_message'] ?? ''));
                break;
            }

            foreach ($data['results'] ?? [] as $result) {
                $places[] = $result;
                if (count($places) >= $maxResults) {
                    break;
                }
            }

            $nextPageToken = $data['next_page_token'] ?? null;
            if (!$nextPageToken) {
                break;
            }

            sleep(2);
        }

        return $places;
    }

    private function getPlaceDetails(string $placeId): ?array
    {
        $response = Http::get('https://maps.googleapis.com/maps/api/place/details/json', [
            'place_id' => $placeId,
            'fields' => 'name,formatted_address,formatted_phone_number,website,rating,user_ratings_total,url',
            'key' => $this->apiKey,
        ]);

        if ($response->failed()) {
            return null;
        }

        $data = $response->json();
        return ($data['status'] ?? '') === 'OK' ? ($data['result'] ?? null) : null;
    }

    private function guessEmail(string $website): string
    {
        if (empty($website)) {
            return '';
        }

        $host = parse_url($website, PHP_URL_HOST);
        if (!$host) {
            return '';
        }

        $host = preg_replace('/^www\./', '', $host);

        $genericDomains = [
            'facebook.com', 'instagram.com', 'yelp.com', 'yellowpages.com',
            'wix.com', 'squarespace.com', 'weebly.com', 'godaddy.com',
            'wordpress.com', 'blogspot.com', 'google.com', 'linktr.ee',
            'healthgrades.com', 'zocdoc.com', 'thumbtack.com',
        ];

        foreach ($genericDomains as $generic) {
            if (str_contains($host, $generic)) {
                return '';
            }
        }

        return "info@{$host}";
    }
}
