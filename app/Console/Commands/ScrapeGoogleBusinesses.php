<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class ScrapeGoogleBusinesses extends Command
{
    protected $signature = 'scrape:google-businesses
        {query : Search query, e.g. "dentist in Austin TX"}
        {--max-results=60 : Maximum results to fetch (max 60)}
        {--max-reviews=50 : Only include businesses with fewer than this many reviews}
        {--min-rating=0 : Only include businesses with at least this rating}
        {--output=leads.csv : Output CSV filename in storage/app/}';

    protected $description = 'Scrape Google Places for businesses with low review counts — perfect cold outreach leads';

    private string $apiKey;

    public function handle(): int
    {
        $this->apiKey = config('services.google_places.key');

        if (empty($this->apiKey)) {
            $this->error('GOOGLE_PLACES_API_KEY is not set in .env');
            return self::FAILURE;
        }

        $query = $this->argument('query');
        $maxResults = min((int) $this->option('max-results'), 60);
        $maxReviews = (int) $this->option('max-reviews');
        $minRating = (float) $this->option('min-rating');
        $outputFile = basename($this->option('output'));

        $this->info("Searching: \"{$query}\"");
        $this->info("Filter: <{$maxReviews} reviews, >={$minRating} rating");

        $places = $this->searchPlaces($query, $maxResults);

        if (empty($places)) {
            $this->warn('No results found.');
            return self::SUCCESS;
        }

        $this->info(sprintf('Found %d places. Fetching details...', count($places)));

        $leads = [];
        $bar = $this->output->createProgressBar(count($places));
        $bar->start();

        foreach ($places as $place) {
            $detail = $this->getPlaceDetails($place['place_id']);
            $bar->advance();

            if (!$detail) {
                continue;
            }

            $reviewCount = $detail['user_ratings_total'] ?? 0;
            $rating = $detail['rating'] ?? 0;

            if ($reviewCount >= $maxReviews) {
                continue;
            }

            if ($rating < $minRating) {
                continue;
            }

            $leads[] = [
                'name' => $detail['name'] ?? '',
                'address' => $detail['formatted_address'] ?? '',
                'phone' => $detail['formatted_phone_number'] ?? '',
                'website' => $detail['website'] ?? '',
                'rating' => $rating,
                'reviews' => $reviewCount,
                'place_id' => $place['place_id'],
                'google_maps_url' => $detail['url'] ?? '',
            ];
        }

        $bar->finish();
        $this->newLine(2);

        if (empty($leads)) {
            $this->warn('No businesses matched your filters.');
            return self::SUCCESS;
        }

        usort($leads, fn ($a, $b) => $a['reviews'] <=> $b['reviews']);

        $this->writeCsv($leads, $outputFile);

        $this->info(sprintf('Exported %d leads to storage/app/%s', count($leads), $outputFile));

        $this->newLine();
        $this->table(
            ['Name', 'Reviews', 'Rating', 'Phone', 'Website'],
            array_map(fn ($l) => [
                substr($l['name'], 0, 35),
                $l['reviews'],
                $l['rating'],
                $l['phone'],
                $l['website'] ? 'Yes' : '-',
            ], array_slice($leads, 0, 20))
        );

        if (count($leads) > 20) {
            $this->info(sprintf('... and %d more in the CSV.', count($leads) - 20));
        }

        return self::SUCCESS;
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
                $this->error('Google Places API request failed: ' . $response->status());
                break;
            }

            $data = $response->json();

            if (($data['status'] ?? '') !== 'OK' && ($data['status'] ?? '') !== 'ZERO_RESULTS') {
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

            // Google requires a short delay before using next_page_token
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

        if (($data['status'] ?? '') !== 'OK') {
            return null;
        }

        return $data['result'] ?? null;
    }

    private function writeCsv(array $leads, string $filename): void
    {
        $csv = fopen('php://temp', 'r+');
        fputcsv($csv, ['Name', 'Address', 'Phone', 'Website', 'Rating', 'Reviews', 'Place ID', 'Google Maps URL']);

        foreach ($leads as $lead) {
            fputcsv($csv, [
                $lead['name'],
                $lead['address'],
                $lead['phone'],
                $lead['website'],
                $lead['rating'],
                $lead['reviews'],
                $lead['place_id'],
                $lead['google_maps_url'],
            ]);
        }

        rewind($csv);
        $content = stream_get_contents($csv);
        fclose($csv);

        Storage::disk('local')->put($filename, $content);
    }
}
