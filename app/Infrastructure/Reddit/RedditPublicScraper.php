<?php

declare(strict_types=1);

namespace App\Infrastructure\Reddit;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RedditPublicScraper
{
    /**
     * @return array<array{id: string, title: string, selftext: string|null, author: string, url: string, score: int, num_comments: int, created_utc: int}>
     */
    public function searchSubreddit(string $subreddit, string $query, int $limit = 25): array
    {
        $response = Http::withHeaders([
            'User-Agent' => 'QuickFeedback:lurk-helper:v1.0',
        ])->get("https://www.reddit.com/r/{$subreddit}/search.json", [
            'q' => $query,
            'restrict_sr' => 'on',
            'sort' => 'new',
            'limit' => $limit,
            't' => 'day',
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
}
