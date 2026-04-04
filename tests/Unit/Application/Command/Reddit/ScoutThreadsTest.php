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
