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
                : $this->searchViaScraper($subreddit->name, $keywords);

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

    public function hasApiCredentials(): bool
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

    /**
     * @param  string[]  $keywords
     */
    private function searchViaScraper(string $subredditName, array $keywords): array
    {
        $results = [];

        foreach ($keywords as $keyword) {
            $results = array_merge($results, $this->publicScraper->searchSubreddit($subredditName, $keyword));
            usleep(500_000);
        }

        return $results;
    }
}
