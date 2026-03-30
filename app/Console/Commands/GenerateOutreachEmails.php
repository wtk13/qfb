<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class GenerateOutreachEmails extends Command
{
    protected $signature = 'outreach:generate
        {--input=leads.csv : Input CSV file in storage/app/}
        {--output=outreach.csv : Output CSV with personalized emails}
        {--sender-name=Wojtek : Your first name for the email signature}
        {--sender-title=Founder, QuickFeedback : Your title}
        {--category= : Business category for personalization, e.g. "dentist"}
        {--city= : City name for personalization, e.g. "Austin"}';

    protected $description = 'Generate personalized cold outreach emails from scraped leads CSV';

    public function handle(): int
    {
        $inputFile = $this->option('input');
        $outputFile = basename($this->option('output'));
        $senderName = $this->option('sender-name');
        $senderTitle = $this->option('sender-title');
        $category = $this->option('category');
        $city = $this->option('city');

        if (! Storage::disk('local')->exists($inputFile)) {
            $this->error("File not found: storage/app/{$inputFile}");
            $this->info('Run scrape:google-businesses first to generate leads.');

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

        if (empty($leads)) {
            $this->warn('No leads found in CSV.');

            return self::SUCCESS;
        }

        $outreach = [];

        foreach ($leads as $lead) {
            $businessName = $lead['Name'];
            $reviews = (int) $lead['Reviews'];
            $rating = (float) $lead['Rating'];
            $website = $lead['Website'];
            $email = $this->guessEmail($website);

            $subject = $this->generateSubject($businessName, $reviews);
            $body = $this->generateBody(
                $businessName, $reviews, $rating,
                $senderName, $senderTitle, $category, $city
            );

            $outreach[] = [
                'business_name' => $businessName,
                'email' => $email,
                'website' => $website,
                'phone' => $lead['Phone'],
                'reviews' => $reviews,
                'rating' => $rating,
                'subject' => $subject,
                'body' => $body,
            ];
        }

        $this->writeCsv($outreach, $outputFile);

        $withEmail = count(array_filter($outreach, fn ($o) => ! empty($o['email'])));
        $withoutEmail = count($outreach) - $withEmail;

        $this->info(sprintf('Generated %d outreach emails → storage/app/%s', count($outreach), $outputFile));
        $this->info(sprintf('  With guessed email: %d', $withEmail));
        $this->info(sprintf('  Need manual lookup: %d', $withoutEmail));

        $this->newLine();
        $this->table(
            ['Business', 'Reviews', 'Email', 'Subject'],
            array_map(fn ($o) => [
                substr($o['business_name'], 0, 30),
                $o['reviews'],
                $o['email'] ?: '(find manually)',
                substr($o['subject'], 0, 50),
            ], array_slice($outreach, 0, 10))
        );

        return self::SUCCESS;
    }

    private function generateSubject(string $businessName, int $reviews): string
    {
        if ($reviews === 0) {
            return "{$businessName}'s Google profile — one thing I noticed";
        }

        if ($reviews < 10) {
            return "{$businessName} — quick thought on your Google reviews";
        }

        return "Idea for {$businessName} to get more 5-star reviews";
    }

    private function generateBody(
        string $businessName,
        int $reviews,
        float $rating,
        string $senderName,
        string $senderTitle,
        ?string $category,
        ?string $city,
    ): string {
        $competitorPhrase = $this->buildCompetitorPhrase($category, $city);

        $reviewLine = match (true) {
            $reviews === 0 => "I came across {$businessName} on Google and noticed you don't have any reviews yet. That's actually a huge opportunity — most customers check reviews before calling, so even a handful puts you ahead of the pack.",
            $reviews < 10 => "I came across {$businessName} on Google Maps — {$reviews} reviews at {$rating} stars. Looking at {$competitorPhrase}, most have 30-50+. The gap is almost never about service quality. It's that most customers need a nudge.",
            default => "I was looking at {$businessName}'s Google profile — {$reviews} reviews at {$rating} stars is solid. But {$competitorPhrase} have significantly more, which pushes them higher in local search results.",
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
        if ($category && $city) {
            $plural = $this->pluralizeCategory($category);

            return "other {$plural} in {$city}";
        }

        if ($category) {
            $plural = $this->pluralizeCategory($category);

            return "other {$plural} in your area";
        }

        if ($city) {
            return "similar businesses in {$city}";
        }

        return 'your competitors in the area';
    }

    private function pluralizeCategory(string $category): string
    {
        return match (strtolower($category)) {
            'dentist' => 'dentists',
            'plumber' => 'plumbers',
            'restaurant' => 'restaurants',
            'salon' => 'salons',
            'barber' => 'barbers',
            'chiropractor' => 'chiropractors',
            'veterinarian', 'vet' => 'vets',
            'gym' => 'gyms',
            'mechanic', 'auto repair' => 'auto shops',
            'lawyer', 'attorney' => 'law firms',
            'accountant' => 'accountants',
            'realtor', 'real estate' => 'realtors',
            'cleaning' => 'cleaning companies',
            'electrician' => 'electricians',
            'roofer' => 'roofers',
            'hvac' => 'HVAC companies',
            'landscaper' => 'landscapers',
            default => "{$category}s",
        };
    }

    private function guessEmail(string $website): string
    {
        if (empty($website)) {
            return '';
        }

        $host = parse_url($website, PHP_URL_HOST);

        if (! $host) {
            return '';
        }

        $host = preg_replace('/^www\./', '', $host);

        // Skip generic platforms — can't guess email from these
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

    private function writeCsv(array $rows, string $filename): void
    {
        $csv = fopen('php://temp', 'r+');
        fputcsv($csv, ['Business Name', 'Email', 'Website', 'Phone', 'Reviews', 'Rating', 'Subject', 'Body']);

        foreach ($rows as $row) {
            fputcsv($csv, [
                $row['business_name'],
                $row['email'],
                $row['website'],
                $row['phone'],
                $row['reviews'],
                $row['rating'],
                $row['subject'],
                $row['body'],
            ]);
        }

        rewind($csv);
        $content = stream_get_contents($csv);
        fclose($csv);

        Storage::disk('local')->put($filename, $content);
    }
}
