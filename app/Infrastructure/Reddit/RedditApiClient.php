<?php

declare(strict_types=1);

namespace App\Infrastructure\Reddit;

use Domain\Reddit\Port\RedditApiInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RedditApiClient implements RedditApiInterface
{
    private const string TOKEN_CACHE_KEY = 'reddit_oauth_token';

    private const int TOKEN_TTL_SECONDS = 3300; // 55 minutes

    public function searchSubreddit(string $subreddit, string $query, int $limit = 25): array
    {
        $token = $this->getAccessToken();

        $response = Http::withToken($token)
            ->withHeaders(['User-Agent' => config('reddit.user_agent')])
            ->get("https://oauth.reddit.com/r/{$subreddit}/search.json", [
                'q' => $query,
                'restrict_sr' => 'on',
                'sort' => 'new',
                'limit' => $limit,
                't' => 'day',
            ]);

        if ($response->failed()) {
            Log::warning('Reddit API search failed', [
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
            'created_utc' => $child['data']['created_utc'],
        ], $children);
    }

    public function submitComment(string $parentThingId, string $body): string
    {
        if (config('reddit.dry_run')) {
            Log::info('Reddit dry run: would submit comment', ['parent' => $parentThingId]);

            return 'dry_run_'.uniqid();
        }

        $token = $this->getAccessToken();

        $response = Http::withToken($token)
            ->withHeaders(['User-Agent' => config('reddit.user_agent')])
            ->asForm()
            ->post('https://oauth.reddit.com/api/comment', [
                'thing_id' => $parentThingId,
                'text' => $body,
            ]);

        if ($response->failed()) {
            Log::error('Reddit API submit comment failed', ['status' => $response->status()]);
            throw new \RuntimeException('Failed to submit comment: '.$response->status());
        }

        return $response->json('json.data.things.0.data.name', '');
    }

    public function submitPost(string $subreddit, string $title, string $body): string
    {
        if (config('reddit.dry_run')) {
            Log::info('Reddit dry run: would submit post', ['subreddit' => $subreddit, 'title' => $title]);

            return 'dry_run_'.uniqid();
        }

        $token = $this->getAccessToken();

        $response = Http::withToken($token)
            ->withHeaders(['User-Agent' => config('reddit.user_agent')])
            ->asForm()
            ->post('https://oauth.reddit.com/api/submit', [
                'sr' => $subreddit,
                'kind' => 'self',
                'title' => $title,
                'text' => $body,
            ]);

        if ($response->failed()) {
            Log::error('Reddit API submit post failed', ['status' => $response->status()]);
            throw new \RuntimeException('Failed to submit post: '.$response->status());
        }

        return $response->json('json.data.name', '');
    }

    private function getAccessToken(): string
    {
        return Cache::remember(self::TOKEN_CACHE_KEY, self::TOKEN_TTL_SECONDS, function () {
            $response = Http::asForm()
                ->withBasicAuth(config('reddit.client_id'), config('reddit.client_secret'))
                ->withHeaders(['User-Agent' => config('reddit.user_agent')])
                ->post('https://www.reddit.com/api/v1/access_token', [
                    'grant_type' => 'password',
                    'username' => config('reddit.username'),
                    'password' => config('reddit.password'),
                ]);

            if ($response->failed()) {
                throw new \RuntimeException('Failed to obtain Reddit access token: '.$response->status());
            }

            return $response->json('access_token');
        });
    }
}
