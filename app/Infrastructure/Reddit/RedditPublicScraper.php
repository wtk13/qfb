<?php

declare(strict_types=1);

namespace App\Infrastructure\Reddit;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RedditPublicScraper
{
    /**
     * Fetch recent posts from a subreddit and filter locally by keywords.
     *
     * @param  string[]  $keywords
     * @return list<array{id: string, title: string, selftext: string|null, author: string, url: string, score: int, num_comments: int, created_utc: int}>
     */
    public function fetchNewPosts(string $subreddit, array $keywords, int $limit = 100): array
    {
        if ($keywords === []) {
            return [];
        }

        $posts = $this->fetchListing($subreddit, $limit);

        if ($posts === []) {
            return [];
        }

        $pattern = $this->buildKeywordPattern($keywords);

        return array_values(array_filter($posts, function (array $post) use ($pattern) {
            $haystack = $post['title'].' '.($post['selftext'] ?? '');

            return preg_match($pattern, $haystack) === 1;
        }));
    }

    /**
     * @return list<array{id: string, title: string, selftext: string|null, author: string, url: string, score: int, num_comments: int, created_utc: int}>
     */
    private function fetchListing(string $subreddit, int $limit): array
    {
        $response = Http::timeout(15)->withHeaders([
            'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36',
            'Accept' => 'application/json',
        ])->get("https://old.reddit.com/r/{$subreddit}/new.json", [
            'limit' => $limit,
            'raw_json' => 1,
        ]);

        if ($response->failed()) {
            Log::warning('Reddit public scrape failed', [
                'subreddit' => $subreddit,
                'status' => $response->status(),
            ]);

            return [];
        }

        $children = $response->json('data.children', []);

        return array_map(fn (array $child) => [
            'id' => $child['data']['name'],
            'title' => $child['data']['title'],
            'selftext' => $child['data']['selftext'] ?? null,
            'author' => $child['data']['author'],
            'url' => 'https://reddit.com'.$child['data']['permalink'],
            'score' => $child['data']['score'],
            'num_comments' => $child['data']['num_comments'],
            'created_utc' => (int) $child['data']['created_utc'],
        ], $children);
    }

    /**
     * @param  string[]  $keywords
     */
    private function buildKeywordPattern(array $keywords): string
    {
        $escaped = array_map(fn (string $kw) => preg_quote($kw, '/'), $keywords);

        return '/'.implode('|', $escaped).'/i';
    }
}
