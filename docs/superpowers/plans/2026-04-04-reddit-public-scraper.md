# Reddit Public Scraper Fallback -- Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Make `reddit:scout` work without Reddit API credentials by falling back to public JSON feed scraping, so threads can be discovered during the lurk phase without API access.

**Architecture:** Add a `RedditPublicScraper` class that implements the same return format as `RedditApiInterface::searchSubreddit()` but uses Reddit's public `.json` endpoints (no auth). Modify `ScoutThreads` to use the scraper when API credentials aren't configured, and filter results by keywords client-side (since public feeds don't support keyword search).

**Tech Stack:** Laravel HTTP client, Reddit public JSON endpoints (`/r/{sub}/new.json`)

---

### Task 1: Create RedditPublicScraper

**Files:**
- Create: `app/Infrastructure/Reddit/RedditPublicScraper.php`
- Test: `tests/Unit/Infrastructure/Reddit/RedditPublicScraperTest.php`

- [ ] **Step 1: Write the test for fetching and filtering subreddit posts**

```php
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
                                'title' => 'Dealing with negative review on Google',
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

        // Verify shape matches RedditApiInterface::searchSubreddit return format
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
```

- [ ] **Step 2: Run test to verify it fails**

Run: `./vendor/bin/sail artisan test --filter=RedditPublicScraperTest`
Expected: FAIL -- class `RedditPublicScraper` not found.

- [ ] **Step 3: Implement RedditPublicScraper**

```php
<?php

declare(strict_types=1);

namespace App\Infrastructure\Reddit;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RedditPublicScraper
{
    /**
     * Scrape a subreddit's public JSON feed and filter by keywords.
     *
     * @param  string[]  $keywords
     * @return array<array{id: string, title: string, selftext: string|null, author: string, url: string, score: int, num_comments: int, created_utc: int}>
     */
    public function scrapeSubreddit(string $subreddit, array $keywords, int $limit = 25): array
    {
        $response = Http::withHeaders([
            'User-Agent' => 'QuickFeedback:lurk-helper:v1.0',
        ])->get("https://www.reddit.com/r/{$subreddit}/new.json", [
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

        $posts = array_map(fn (array $child) => [
            'id' => $child['data']['name'],
            'title' => $child['data']['title'],
            'selftext' => $child['data']['selftext'] ?? null,
            'author' => $child['data']['author'],
            'url' => 'https://reddit.com' . $child['data']['permalink'],
            'score' => $child['data']['score'],
            'num_comments' => $child['data']['num_comments'],
            'created_utc' => (int) $child['data']['created_utc'],
        ], $children);

        return array_values(array_filter($posts, fn (array $post) => $this->matchesKeywords($post, $keywords)));
    }

    /**
     * @param  string[]  $keywords
     */
    private function matchesKeywords(array $post, array $keywords): bool
    {
        $text = strtolower($post['title'] . ' ' . ($post['selftext'] ?? ''));

        foreach ($keywords as $keyword) {
            if (str_contains($text, strtolower($keyword))) {
                return true;
            }
        }

        return false;
    }
}
```

- [ ] **Step 4: Run test to verify it passes**

Run: `./vendor/bin/sail artisan test --filter=RedditPublicScraperTest`
Expected: 3 tests pass.

- [ ] **Step 5: Commit**

```bash
git add app/Infrastructure/Reddit/RedditPublicScraper.php tests/Unit/Infrastructure/Reddit/RedditPublicScraperTest.php
git commit -m "feat(reddit): add public JSON scraper for no-API fallback"
```

---

### Task 2: Modify ScoutThreads to use public scraper fallback

**Files:**
- Modify: `app/Application/Command/Reddit/ScoutThreads.php`
- Test: `tests/Unit/Application/Command/Reddit/ScoutThreadsTest.php`

- [ ] **Step 1: Write the test for fallback behavior**

```php
<?php

namespace Tests\Unit\Application\Command\Reddit;

use App\Application\Command\Reddit\ScoutThreads;
use App\Infrastructure\Reddit\RedditPublicScraper;
use Domain\Reddit\Entity\RedditSubreddit;
use Domain\Reddit\Port\RedditApiInterface;
use Domain\Reddit\Port\RedditSubredditRepositoryInterface;
use Domain\Reddit\Port\RedditThreadRepositoryInterface;
use Tests\TestCase;

class ScoutThreadsTest extends TestCase
{
    public function test_uses_api_when_credentials_configured(): void
    {
        config(['reddit.client_id' => 'test_id', 'reddit.client_secret' => 'test_secret']);

        $subreddit = new RedditSubreddit(
            id: 1,
            name: 'smallbusiness',
            tier: 1,
            maxPostsPerWeek: 2,
            maxCommentsPerWeek: 7,
            rulesJson: null,
            keywordsJson: ['reviews'],
            isActive: true,
        );

        $subredditRepo = $this->createMock(RedditSubredditRepositoryInterface::class);
        $subredditRepo->method('findActive')->willReturn([$subreddit]);

        $threadRepo = $this->createMock(RedditThreadRepositoryInterface::class);
        $threadRepo->method('findByRedditId')->willReturn(null);
        $threadRepo->method('save')->willReturnCallback(fn ($t) => $t);

        $api = $this->createMock(RedditApiInterface::class);
        $api->expects($this->once())
            ->method('searchSubreddit')
            ->with('smallbusiness', 'reviews')
            ->willReturn([
                [
                    'id' => 't3_abc',
                    'title' => 'Need more reviews',
                    'selftext' => 'Help',
                    'author' => 'user1',
                    'url' => 'https://reddit.com/r/smallbusiness/comments/abc/test/',
                    'score' => 5,
                    'num_comments' => 3,
                    'created_utc' => time() - 3600,
                ],
            ]);

        $scraper = $this->createMock(RedditPublicScraper::class);
        $scraper->expects($this->never())->method('scrapeSubreddit');

        $command = new ScoutThreads($api, $subredditRepo, $threadRepo, $scraper);
        $count = $command->execute();

        $this->assertSame(1, $count);
    }

    public function test_falls_back_to_scraper_when_no_api_credentials(): void
    {
        config(['reddit.client_id' => null, 'reddit.client_secret' => null]);

        $subreddit = new RedditSubreddit(
            id: 1,
            name: 'smallbusiness',
            tier: 1,
            maxPostsPerWeek: 2,
            maxCommentsPerWeek: 7,
            rulesJson: null,
            keywordsJson: ['reviews', 'feedback'],
            isActive: true,
        );

        $subredditRepo = $this->createMock(RedditSubredditRepositoryInterface::class);
        $subredditRepo->method('findActive')->willReturn([$subreddit]);

        $threadRepo = $this->createMock(RedditThreadRepositoryInterface::class);
        $threadRepo->method('findByRedditId')->willReturn(null);
        $threadRepo->method('save')->willReturnCallback(fn ($t) => $t);

        $api = $this->createMock(RedditApiInterface::class);
        $api->expects($this->never())->method('searchSubreddit');

        $scraper = $this->createMock(RedditPublicScraper::class);
        $scraper->expects($this->once())
            ->method('scrapeSubreddit')
            ->with('smallbusiness', ['reviews', 'feedback'], 25)
            ->willReturn([
                [
                    'id' => 't3_xyz',
                    'title' => 'How to get customer feedback',
                    'selftext' => 'New business owner here',
                    'author' => 'newbie',
                    'url' => 'https://reddit.com/r/smallbusiness/comments/xyz/feedback/',
                    'score' => 10,
                    'num_comments' => 4,
                    'created_utc' => time() - 1800,
                ],
            ]);

        $command = new ScoutThreads($api, $subredditRepo, $threadRepo, $scraper);
        $count = $command->execute();

        $this->assertSame(1, $count);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `./vendor/bin/sail artisan test --filter=ScoutThreadsTest`
Expected: FAIL -- `ScoutThreads::__construct()` doesn't accept a 4th argument yet.

- [ ] **Step 3: Modify ScoutThreads to accept scraper and use fallback logic**

Replace `app/Application/Command/Reddit/ScoutThreads.php` with:

```php
<?php

declare(strict_types=1);

namespace App\Application\Command\Reddit;

use App\Infrastructure\Reddit\RedditPublicScraper;
use Domain\Reddit\Entity\RedditThread;
use Domain\Reddit\Port\RedditApiInterface;
use Domain\Reddit\Port\RedditSubredditRepositoryInterface;
use Domain\Reddit\Port\RedditThreadRepositoryInterface;
use Domain\Reddit\ValueObject\ThreadStatus;
use Domain\Reddit\ValueObject\ThreadType;

class ScoutThreads
{
    public function __construct(
        private RedditApiInterface $redditApi,
        private RedditSubredditRepositoryInterface $subredditRepo,
        private RedditThreadRepositoryInterface $threadRepo,
        private RedditPublicScraper $publicScraper,
    ) {}

    public function execute(): int
    {
        $subreddits = $this->subredditRepo->findActive();
        $useApi = $this->hasApiCredentials();
        $newCount = 0;

        foreach ($subreddits as $subreddit) {
            $keywords = $subreddit->keywordsJson ?? ['reviews', 'feedback', 'reputation'];

            $results = $useApi
                ? $this->searchViaApi($subreddit->name, $keywords)
                : $this->publicScraper->scrapeSubreddit($subreddit->name, $keywords, 25);

            foreach ($results as $result) {
                if ($this->threadRepo->findByRedditId($result['id'])) {
                    continue;
                }

                if ($result['score'] < 2) {
                    continue;
                }

                $hoursSince = (time() - $result['created_utc']) / 3600;
                if ($hoursSince > 24) {
                    continue;
                }

                $thread = new RedditThread(
                    id: 0,
                    subredditId: $subreddit->id,
                    redditId: $result['id'],
                    title: $result['title'],
                    body: $result['selftext'],
                    author: $result['author'],
                    url: $result['url'],
                    score: $result['score'],
                    numComments: $result['num_comments'],
                    threadType: ThreadType::classify($result['title'], $result['selftext']),
                    status: ThreadStatus::New,
                    discoveredAt: new \DateTimeImmutable,
                    createdAt: new \DateTimeImmutable,
                );

                $this->threadRepo->save($thread);
                $newCount++;
            }

            sleep(1);
        }

        return $newCount;
    }

    private function hasApiCredentials(): bool
    {
        return config('reddit.client_id') && config('reddit.client_secret');
    }

    /**
     * @param  string[]  $keywords
     */
    private function searchViaApi(string $subredditName, array $keywords): array
    {
        $results = [];

        foreach ($keywords as $keyword) {
            $results = array_merge($results, $this->redditApi->searchSubreddit($subredditName, $keyword));
            usleep(500_000);
        }

        return $results;
    }
}
```

- [ ] **Step 4: Run test to verify it passes**

Run: `./vendor/bin/sail artisan test --filter=ScoutThreadsTest`
Expected: 2 tests pass.

- [ ] **Step 5: Run existing Reddit tests to verify no regressions**

Run: `./vendor/bin/sail artisan test --filter=Reddit`
Expected: All pass.

- [ ] **Step 6: Commit**

```bash
git add app/Application/Command/Reddit/ScoutThreads.php tests/Unit/Application/Command/Reddit/ScoutThreadsTest.php
git commit -m "feat(reddit): add public scraper fallback to scout command"
```

---

### Task 3: Update console command output to indicate scraping mode

**Files:**
- Modify: `app/Console/Commands/Reddit/ScoutRedditThreads.php`

- [ ] **Step 1: Update the command to show which mode is active**

Replace `app/Console/Commands/Reddit/ScoutRedditThreads.php` with:

```php
<?php

namespace App\Console\Commands\Reddit;

use App\Application\Command\Reddit\ScoutThreads;
use Illuminate\Console\Command;

class ScoutRedditThreads extends Command
{
    protected $signature = 'reddit:scout';

    protected $description = 'Scout Reddit for relevant threads across target subreddits';

    public function handle(ScoutThreads $command): int
    {
        if (! config('reddit.enabled')) {
            $this->info('Reddit agent is disabled.');

            return self::SUCCESS;
        }

        $mode = config('reddit.client_id') && config('reddit.client_secret')
            ? 'API'
            : 'public scraper (no API credentials)';

        $this->info("Scouting Reddit for new threads via {$mode}...");

        $count = $command->execute();

        $this->info("Found {$count} new threads.");

        return self::SUCCESS;
    }
}
```

- [ ] **Step 2: Run all Reddit tests**

Run: `./vendor/bin/sail artisan test --filter=Reddit`
Expected: All pass.

- [ ] **Step 3: Commit**

```bash
git add app/Console/Commands/Reddit/ScoutRedditThreads.php
git commit -m "feat(reddit): show scraping mode in scout command output"
```

---

### Task 4: Manual smoke test

- [ ] **Step 1: Enable the Reddit agent**

Run: `./vendor/bin/sail artisan tinker --execute="config(['reddit.enabled' => true]); echo 'ok';"`

Make sure `REDDIT_ENABLED=true` is set in `.env` and `REDDIT_CLIENT_ID` / `REDDIT_CLIENT_SECRET` are empty or absent.

- [ ] **Step 2: Run the scout command**

Run: `./vendor/bin/sail artisan reddit:scout`

Expected output:
```
Scouting Reddit for new threads via public scraper (no API credentials)...
Found N new threads.
```

- [ ] **Step 3: Verify threads in dashboard**

Open `/admin/reddit` in browser and check that discovered threads appear.
