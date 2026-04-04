<?php

namespace Tests\Unit\Infrastructure\Reddit;

use App\Infrastructure\Reddit\RedditPublicScraper;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class RedditPublicScraperTest extends TestCase
{
    public function test_searches_subreddit_via_public_endpoint(): void
    {
        Http::fake([
            'https://www.reddit.com/r/smallbusiness/search.json*' => Http::response([
                'data' => [
                    'children' => [
                        [
                            'data' => [
                                'name' => 't3_abc123',
                                'title' => 'How to get more Google reviews for my shop',
                                'selftext' => 'I just opened a bakery and need help.',
                                'author' => 'baker42',
                                'permalink' => '/r/smallbusiness/comments/abc123/how_to_get/',
                                'score' => 15,
                                'num_comments' => 8,
                                'created_utc' => time() - 3600,
                            ],
                        ],
                    ],
                ],
            ]),
        ]);

        $scraper = new RedditPublicScraper;

        $results = $scraper->searchSubreddit('smallbusiness', 'google reviews');

        $this->assertCount(1, $results);

        $first = $results[0];
        $this->assertSame('t3_abc123', $first['id']);
        $this->assertSame('How to get more Google reviews for my shop', $first['title']);
        $this->assertSame('baker42', $first['author']);
        $this->assertStringStartsWith('https://reddit.com/', $first['url']);
        $this->assertArrayHasKey('selftext', $first);
        $this->assertArrayHasKey('score', $first);
        $this->assertArrayHasKey('num_comments', $first);
        $this->assertArrayHasKey('created_utc', $first);
    }

    public function test_returns_empty_array_on_http_failure(): void
    {
        Http::fake([
            'https://www.reddit.com/r/smallbusiness/search.json*' => Http::response('error', 429),
        ]);

        $scraper = new RedditPublicScraper;

        $results = $scraper->searchSubreddit('smallbusiness', 'reviews');

        $this->assertSame([], $results);
    }
}
