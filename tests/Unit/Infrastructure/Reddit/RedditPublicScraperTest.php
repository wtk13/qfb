<?php

namespace Tests\Unit\Infrastructure\Reddit;

use App\Infrastructure\Reddit\RedditPublicScraper;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class RedditPublicScraperTest extends TestCase
{
    public function test_scrapes_subreddit_and_filters_by_keywords(): void
    {
        Http::fake([
            'https://www.reddit.com/r/smallbusiness/new.json*' => Http::response([
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
                        [
                            'data' => [
                                'name' => 't3_def456',
                                'title' => 'Best accounting software?',
                                'selftext' => 'Looking for bookkeeping tools.',
                                'author' => 'numbers99',
                                'permalink' => '/r/smallbusiness/comments/def456/accounting/',
                                'score' => 20,
                                'num_comments' => 12,
                                'created_utc' => time() - 7200,
                            ],
                        ],
                        [
                            'data' => [
                                'name' => 't3_ghi789',
                                'title' => 'Dealing with negative reviews on Google',
                                'selftext' => 'A customer left a 1-star review unfairly.',
                                'author' => 'stressed_owner',
                                'permalink' => '/r/smallbusiness/comments/ghi789/negative/',
                                'score' => 5,
                                'num_comments' => 3,
                                'created_utc' => time() - 1800,
                            ],
                        ],
                    ],
                ],
            ]),
        ]);

        $scraper = new RedditPublicScraper;

        $results = $scraper->scrapeSubreddit('smallbusiness', ['reviews', 'google reviews'], 25);

        $this->assertCount(2, $results);
        $this->assertSame('t3_abc123', $results[0]['id']);
        $this->assertSame('t3_ghi789', $results[1]['id']);

        $first = $results[0];
        $this->assertArrayHasKey('id', $first);
        $this->assertArrayHasKey('title', $first);
        $this->assertArrayHasKey('selftext', $first);
        $this->assertArrayHasKey('author', $first);
        $this->assertArrayHasKey('url', $first);
        $this->assertArrayHasKey('score', $first);
        $this->assertArrayHasKey('num_comments', $first);
        $this->assertArrayHasKey('created_utc', $first);
        $this->assertStringStartsWith('https://reddit.com/', $first['url']);
    }

    public function test_returns_empty_array_on_http_failure(): void
    {
        Http::fake([
            'https://www.reddit.com/r/smallbusiness/new.json*' => Http::response('error', 429),
        ]);

        $scraper = new RedditPublicScraper;

        $results = $scraper->scrapeSubreddit('smallbusiness', ['reviews'], 25);

        $this->assertSame([], $results);
    }

    public function test_keyword_matching_is_case_insensitive(): void
    {
        Http::fake([
            'https://www.reddit.com/r/SEO/new.json*' => Http::response([
                'data' => [
                    'children' => [
                        [
                            'data' => [
                                'name' => 't3_aaa111',
                                'title' => 'LOCAL SEO tips for beginners',
                                'selftext' => '',
                                'author' => 'seo_guru',
                                'permalink' => '/r/SEO/comments/aaa111/local_seo/',
                                'score' => 10,
                                'num_comments' => 5,
                                'created_utc' => time() - 600,
                            ],
                        ],
                    ],
                ],
            ]),
        ]);

        $scraper = new RedditPublicScraper;

        $results = $scraper->scrapeSubreddit('SEO', ['local seo'], 25);

        $this->assertCount(1, $results);
    }
}
