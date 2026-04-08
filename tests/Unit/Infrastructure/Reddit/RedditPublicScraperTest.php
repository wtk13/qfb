<?php

namespace Tests\Unit\Infrastructure\Reddit;

use App\Infrastructure\Reddit\RedditPublicScraper;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class RedditPublicScraperTest extends TestCase
{
    public function test_fetches_new_posts_and_filters_by_keywords(): void
    {
        Http::fake([
            'https://old.reddit.com/r/smallbusiness/new.json*' => Http::response([
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
                                'selftext' => 'Looking for recommendations.',
                                'author' => 'accountant99',
                                'permalink' => '/r/smallbusiness/comments/def456/accounting/',
                                'score' => 10,
                                'num_comments' => 5,
                                'created_utc' => time() - 7200,
                            ],
                        ],
                    ],
                ],
            ]),
        ]);

        $scraper = new RedditPublicScraper;

        $results = $scraper->fetchNewPosts('smallbusiness', ['google reviews', 'feedback']);

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
            'https://old.reddit.com/r/smallbusiness/new.json*' => Http::response('error', 429),
        ]);

        $scraper = new RedditPublicScraper;

        $results = $scraper->fetchNewPosts('smallbusiness', ['reviews']);

        $this->assertSame([], $results);
    }

    public function test_returns_empty_when_no_keywords_match(): void
    {
        Http::fake([
            'https://old.reddit.com/r/smallbusiness/new.json*' => Http::response([
                'data' => [
                    'children' => [
                        [
                            'data' => [
                                'name' => 't3_nomatch',
                                'title' => 'Best pizza in town',
                                'selftext' => 'Where do you go for pizza?',
                                'author' => 'foodie',
                                'permalink' => '/r/smallbusiness/comments/nomatch/pizza/',
                                'score' => 20,
                                'num_comments' => 12,
                                'created_utc' => time() - 1800,
                            ],
                        ],
                    ],
                ],
            ]),
        ]);

        $scraper = new RedditPublicScraper;

        $results = $scraper->fetchNewPosts('smallbusiness', ['reviews', 'reputation']);

        $this->assertSame([], $results);
    }
}
