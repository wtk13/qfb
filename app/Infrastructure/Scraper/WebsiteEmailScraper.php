<?php

namespace App\Infrastructure\Scraper;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WebsiteEmailScraper
{
    private const TIMEOUT = 8;

    private const CONTACT_PATHS = [
        '/contact',
        '/contact-us',
        '/about',
        '/about-us',
    ];

    public const GENERIC_DOMAINS = [
        'facebook.com', 'instagram.com', 'yelp.com', 'yellowpages.com',
        'wix.com', 'squarespace.com', 'weebly.com', 'godaddy.com',
        'wordpress.com', 'blogspot.com', 'google.com', 'linktr.ee',
        'healthgrades.com', 'zocdoc.com', 'thumbtack.com', 'angi.com',
        'homeadvisor.com', 'bbb.org', 'mapquest.com', 'superpages.com',
    ];

    private const JUNK_PREFIXES = [
        'sentry-', 'wixpress', 'noreply', 'no-reply', 'mailer-daemon',
        'postmaster', 'abuse', 'webmaster', 'hostmaster', 'root@',
    ];

    /**
     * Scrape a business website for email addresses.
     * Returns the best email found, or empty string if none.
     */
    public function scrape(string $website): string
    {
        $host = $this->extractHost($website);
        if (! $host) {
            return '';
        }

        $baseUrl = $this->normalizeBaseUrl($website);
        $emails = [];

        // Scrape homepage first
        $homepageEmails = $this->scrapeUrl($baseUrl, $host);
        $emails = array_merge($emails, $homepageEmails);

        // If no emails on homepage, try contact/about pages
        if (empty($emails)) {
            foreach (self::CONTACT_PATHS as $path) {
                $pageEmails = $this->scrapeUrl($baseUrl.$path, $host);
                $emails = array_merge($emails, $pageEmails);

                if (! empty($emails)) {
                    break;
                }
            }
        }

        $emails = array_unique($emails);

        if (empty($emails)) {
            return '';
        }

        return $this->pickBestEmail($emails, $host);
    }

    private function scrapeUrl(string $url, string $host): array
    {
        if (! $this->isSafeUrl($url)) {
            return [];
        }

        try {
            $response = Http::timeout(self::TIMEOUT)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (compatible; QuickFeedbackBot/1.0)',
                    'Accept' => 'text/html',
                ])
                ->get($url);

            if ($response->failed()) {
                return [];
            }

            $html = $response->body();

            // Limit to 500KB to avoid parsing huge pages
            if (strlen($html) > 512000) {
                $html = substr($html, 0, 512000);
            }

            return $this->extractEmails($html, $host);
        } catch (\Exception $e) {
            Log::debug('WebsiteEmailScraper: failed to fetch '.$url, ['error' => $e->getMessage()]);

            return [];
        }
    }

    private function extractEmails(string $html, string $host): array
    {
        $emails = [];

        // 1. Extract from mailto: links (highest confidence)
        if (preg_match_all('/mailto:([a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,})/i', $html, $matches)) {
            foreach ($matches[1] as $email) {
                $emails[] = strtolower($email);
            }
        }

        // 2. Extract from visible text (broader regex)
        // Strip HTML tags first to avoid matching attributes
        $text = strip_tags($html);
        if (preg_match_all('/[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}/', $text, $matches)) {
            foreach ($matches[0] as $email) {
                $emails[] = strtolower($email);
            }
        }

        // 3. Look for obfuscated emails: "name [at] domain [dot] com"
        if (preg_match_all('/([a-zA-Z0-9._%+\-]+)\s*[\[\(]\s*at\s*[\]\)]\s*([a-zA-Z0-9.\-]+)\s*[\[\(]\s*dot\s*[\]\)]\s*([a-zA-Z]{2,})/i', $text, $matches)) {
            foreach ($matches[0] as $i => $match) {
                $emails[] = strtolower($matches[1][$i].'@'.$matches[2][$i].'.'.$matches[3][$i]);
            }
        }

        return array_values(array_unique(array_filter($emails, fn ($e) => $this->isValidEmail($e, $host))));
    }

    private function isValidEmail(string $email, string $host): bool
    {
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $domain = substr(strrchr($email, '@'), 1);

        // Reject emails from generic/platform domains
        foreach (self::GENERIC_DOMAINS as $generic) {
            if (str_contains($domain, $generic)) {
                return false;
            }
        }

        // Reject common junk prefixes
        $local = substr($email, 0, strpos($email, '@'));
        foreach (self::JUNK_PREFIXES as $junk) {
            if (str_starts_with($local, $junk)) {
                return false;
            }
        }

        // Reject placeholder/example emails from templates and boilerplate
        $placeholders = [
            'user@domain.com', 'user@example.com', 'email@domain.com',
            'email@example.com', 'name@domain.com', 'name@example.com',
            'your@email.com', 'youremail@domain.com', 'test@test.com',
            'mail@example.com', 'example@example.com', 'john@doe.com',
            'jane@doe.com', 'someone@example.com', 'username@domain.com',
        ];
        if (in_array($email, $placeholders, true)) {
            return false;
        }

        // Reject emails from example/placeholder domains
        if (preg_match('/@(example\.|domain\.|test\.|placeholder\.|sample\.)/i', $email)) {
            return false;
        }

        // Reject image file extensions mistakenly captured
        if (preg_match('/\.(png|jpg|jpeg|gif|svg|webp|css|js)$/i', $email)) {
            return false;
        }

        return true;
    }

    /**
     * Rank emails and pick the best one.
     * Prefer: business domain > personal name > generic prefix.
     */
    private function pickBestEmail(array $emails, string $host): string
    {
        $domainEmails = array_filter($emails, fn ($e) => str_ends_with($e, '@'.$host));
        $otherEmails = array_diff($emails, $domainEmails);

        // Prefer emails matching the business domain
        $pool = ! empty($domainEmails) ? $domainEmails : $otherEmails;

        // Score each email — lower is better
        $scored = [];
        foreach ($pool as $email) {
            $local = substr($email, 0, strpos($email, '@'));
            $scored[$email] = $this->scoreEmailPrefix($local);
        }

        asort($scored);

        return array_key_first($scored);
    }

    private function scoreEmailPrefix(string $local): int
    {
        // Personal names are best (owner likely reads them)
        if (preg_match('/^[a-z]+\.[a-z]+$/', $local)) {
            return 1; // john.smith
        }
        if (preg_match('/^[a-z]{2,15}$/', $local) && ! in_array($local, ['info', 'hello', 'contact', 'admin', 'office', 'support', 'sales', 'help'])) {
            return 2; // mike, sarah — likely a person
        }

        // Direct business contact prefixes
        return match ($local) {
            'owner', 'manager' => 3,
            'hello' => 4,
            'contact' => 5,
            'office' => 6,
            'info' => 7,
            'admin' => 8,
            'sales' => 9,
            'support', 'help' => 10,
            default => 6,
        };
    }

    private function extractHost(string $website): string
    {
        $host = parse_url($website, PHP_URL_HOST);
        if (! $host) {
            return '';
        }

        return preg_replace('/^www\./', '', $host);
    }

    private function normalizeBaseUrl(string $website): string
    {
        $parsed = parse_url($website);
        $scheme = $parsed['scheme'] ?? 'https';
        $host = $parsed['host'] ?? '';

        return rtrim("{$scheme}://{$host}", '/');
    }

    private function isSafeUrl(string $url): bool
    {
        $scheme = parse_url($url, PHP_URL_SCHEME);
        if (! in_array($scheme, ['http', 'https'], true)) {
            return false;
        }

        $host = parse_url($url, PHP_URL_HOST);
        if (! $host) {
            return false;
        }

        $ip = gethostbyname($host);

        // gethostbyname returns the hostname if resolution fails
        if ($ip === $host) {
            return false;
        }

        // Block private and reserved IP ranges (SSRF protection)
        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false;
    }
}
