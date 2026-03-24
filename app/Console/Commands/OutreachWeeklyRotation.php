<?php

namespace App\Console\Commands;

use App\Infrastructure\Persistence\Eloquent\OutreachCampaignModel;
use App\Infrastructure\Persistence\Eloquent\OutreachLeadModel;
use App\Infrastructure\Scraper\WebsiteEmailScraper;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class OutreachWeeklyRotation extends Command
{
    protected $signature = 'outreach:weekly
        {--max-results=60 : Max places to scrape per niche}
        {--max-reviews=30 : Only businesses with fewer reviews than this}';

    protected $description = 'Weekly automated rotation: scrape new niches, verify emails, prepare leads for daily sending';

    private string $apiKey;

    private array $categories = [
        // Medical & Health
        'dentist', 'chiropractor', 'veterinarian', 'optometrist',
        'dermatologist', 'physical therapist', 'pediatrician',
        'orthodontist', 'podiatrist', 'psychiatrist', 'allergist',
        'urgent care', 'medical spa', 'acupuncturist', 'hearing aid',
        // Home Services
        'plumber', 'electrician', 'hvac', 'roofer', 'landscaper',
        'cleaning company', 'pest control', 'garage door repair',
        'painting contractor', 'fence company', 'handyman',
        'locksmith', 'tree service', 'carpet cleaning', 'window cleaning',
        'pool service', 'septic service', 'foundation repair',
        'water damage restoration', 'mold removal', 'solar installer',
        'general contractor', 'interior designer', 'home inspector',
        // Auto
        'auto repair', 'auto detailing', 'tire shop',
        'auto body shop', 'oil change', 'car wash', 'towing service',
        'transmission repair', 'auto glass repair',
        // Beauty & Wellness
        'salon', 'barbershop', 'spa', 'nail salon', 'gym',
        'yoga studio', 'massage therapist', 'med spa', 'tattoo shop',
        'tanning salon', 'eyelash extensions', 'waxing salon',
        'pilates studio', 'personal trainer', 'crossfit',
        // Food & Hospitality
        'restaurant', 'cafe', 'bakery', 'catering', 'food truck',
        'pizza', 'sushi', 'mexican restaurant', 'italian restaurant',
        'thai restaurant', 'indian restaurant', 'bbq restaurant',
        'juice bar', 'ice cream shop', 'brewery', 'wine bar',
        // Professional Services
        'accountant', 'real estate agent', 'insurance agent', 'lawyer',
        'financial advisor', 'photographer', 'wedding planner',
        'tutoring', 'dog groomer', 'pet boarding', 'moving company',
        'storage facility', 'tax preparer', 'notary', 'travel agent',
        'property management', 'mortgage broker', 'home stager',
        // Education & Childcare
        'daycare', 'preschool', 'martial arts', 'dance studio',
        'music school', 'driving school', 'swim lessons',
        // Events & Entertainment
        'dj', 'event planner', 'florist', 'party rental',
        'photo booth', 'videographer', 'escape room',
        // Trades & Specialty
        'appliance repair', 'furniture repair', 'tailor',
        'dry cleaner', 'print shop', 'sign company',
        'computer repair', 'phone repair', 'jewelry repair',
    ];

    private array $cities = [
        // Texas
        'Austin, TX', 'Dallas, TX', 'Houston, TX', 'San Antonio, TX', 'Fort Worth, TX',
        'El Paso, TX', 'Plano, TX', 'Arlington, TX', 'Frisco, TX',
        // Florida
        'Miami, FL', 'Tampa, FL', 'Orlando, FL', 'Jacksonville, FL',
        'St Petersburg, FL', 'Fort Lauderdale, FL', 'Sarasota, FL', 'Naples, FL',
        // California
        'San Diego, CA', 'Sacramento, CA', 'San Jose, CA', 'Fresno, CA',
        'Long Beach, CA', 'Bakersfield, CA', 'Riverside, CA', 'Irvine, CA',
        'Santa Barbara, CA', 'Pasadena, CA',
        // Southeast
        'Atlanta, GA', 'Nashville, TN', 'Memphis, TN', 'Charlotte, NC', 'Raleigh, NC',
        'Charleston, SC', 'Greenville, SC', 'Savannah, GA', 'Knoxville, TN',
        'Birmingham, AL', 'Huntsville, AL', 'New Orleans, LA', 'Baton Rouge, LA',
        'Richmond, VA', 'Virginia Beach, VA',
        // Northeast
        'Philadelphia, PA', 'Pittsburgh, PA', 'Boston, MA', 'Providence, RI',
        'Hartford, CT', 'Newark, NJ', 'Jersey City, NJ', 'Buffalo, NY',
        'Rochester, NY', 'Syracuse, NY', 'Baltimore, MD',
        // Midwest
        'Chicago, IL', 'Columbus, OH', 'Indianapolis, IN', 'Minneapolis, MN',
        'Kansas City, MO', 'St Louis, MO', 'Milwaukee, WI', 'Madison, WI',
        'Detroit, MI', 'Grand Rapids, MI', 'Cincinnati, OH', 'Cleveland, OH',
        'Omaha, NE', 'Des Moines, IA', 'Louisville, KY', 'Lexington, KY',
        // Mountain & West
        'Denver, CO', 'Colorado Springs, CO', 'Phoenix, AZ', 'Scottsdale, AZ',
        'Tucson, AZ', 'Las Vegas, NV', 'Reno, NV', 'Salt Lake City, UT',
        'Boise, ID', 'Portland, OR', 'Seattle, WA', 'Spokane, WA',
        'Albuquerque, NM', 'Oklahoma City, OK', 'Tulsa, OK',
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
            ->update([
                'scraped_at' => now(),
                'new_leads_last_scrape' => $places['new'],
            ]);

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
        // Load all campaigns in one query, keyed by "category|city"
        $campaigns = OutreachCampaignModel::all()
            ->keyBy(fn ($c) => $c->category . '|' . $c->city);

        // Collect never-scraped combos and score scraped ones
        $unscraped = [];
        $candidates = [];

        foreach ($this->categories as $category) {
            foreach ($this->cities as $cityState) {
                [$city, $state] = array_map('trim', explode(',', $cityState));
                $key = $category . '|' . $city;
                $campaign = $campaigns->get($key);

                $entry = ['category' => $category, 'city' => $city, 'state' => $state];

                if (!$campaign || !$campaign->scraped_at) {
                    $unscraped[] = $entry;
                    continue;
                }

                $weeksAgo = $campaign->scraped_at->diffInWeeks(now());

                // Exhausted niches (0 new leads last time) need 12+ weeks cooldown
                $cooldown = ($campaign->new_leads_last_scrape === 0) ? 12 : 4;

                if ($weeksAgo >= $cooldown) {
                    $candidates[] = ['entry' => $entry, 'weeks_ago' => $weeksAgo];
                }
            }
        }

        // Prefer a random never-scraped combo
        if (!empty($unscraped)) {
            return $unscraped[array_rand($unscraped)];
        }

        // Otherwise pick the stalest eligible candidate
        if (!empty($candidates)) {
            usort($candidates, fn ($a, $b) => $b['weeks_ago'] <=> $a['weeks_ago']);
            return $candidates[0]['entry'];
        }

        return null;
    }

    private function scrapeLeads(string $query, int $maxResults, int $maxReviews, string $category, string $city): array
    {
        $emailScraper = app(WebsiteEmailScraper::class);

        // Pre-load all known place_ids to avoid wasting API detail calls on duplicates
        $knownPlaceIds = OutreachLeadModel::pluck('place_id')->flip();

        $places = $this->searchPlaces($query, $maxResults, $knownPlaceIds);
        $total = count($places);
        $new = 0;
        $skipped = 0;
        $scraped = 0;

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        foreach ($places as $place) {
            $bar->advance();

            // Skip if already in DB (checked against pre-loaded set)
            if ($knownPlaceIds->has($place['place_id'])) {
                $skipped++;
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
            $email = '';

            // Try scraping the actual website for a real email
            if ($website && !$this->isGenericDomain($website)) {
                $email = $emailScraper->scrape($website);
                if ($email) {
                    $scraped++;
                }
            }

            // Fall back to info@ guess if scraping found nothing
            if (!$email) {
                $email = $this->guessEmail($website);
            }

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

            // Add to known set so multi-page results don't hit DB again
            $knownPlaceIds[$place['place_id']] = true;
            $new++;
        }

        $bar->finish();
        $this->newLine();

        if ($skipped > 0) {
            $this->warn("  Skipped {$skipped} already-known businesses (saved {$skipped} API detail calls).");
        }
        if ($scraped > 0) {
            $this->info("  Found {$scraped} real emails from website scraping.");
        }

        return ['total' => $total, 'new' => $new];
    }

    private function isGenericDomain(string $website): bool
    {
        $host = parse_url($website, PHP_URL_HOST);
        if (!$host) {
            return true;
        }

        foreach (WebsiteEmailScraper::GENERIC_DOMAINS as $generic) {
            if (str_contains($host, $generic)) {
                return true;
            }
        }

        return false;
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

    private function searchPlaces(string $query, int $maxResults, \Illuminate\Support\Collection $knownPlaceIds): array
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

            $pageResults = $data['results'] ?? [];
            $newOnPage = 0;

            foreach ($pageResults as $result) {
                if (empty($result['place_id'])) {
                    continue;
                }

                $places[] = $result;

                if (!$knownPlaceIds->has($result['place_id'])) {
                    $newOnPage++;
                }

                if (count($places) >= $maxResults) {
                    break;
                }
            }

            // Early exit: if an entire page returned only known businesses,
            // Google is recycling results — stop wasting API calls
            if (count($pageResults) > 0 && $newOnPage === 0) {
                $this->warn('  Entire page was duplicates — stopping early to save API quota.');
                break;
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

        foreach (WebsiteEmailScraper::GENERIC_DOMAINS as $generic) {
            if (str_contains($host, $generic)) {
                return '';
            }
        }

        return "info@{$host}";
    }
}
