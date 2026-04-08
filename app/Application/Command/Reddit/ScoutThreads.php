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
            $keywords = $subreddit->keywordsJson ?? [
                'get more reviews',
                'ask for reviews',
                'Google reviews',
                'negative review',
                'bad review',
                'fake review',
                'review management',
                'reputation management',
                'review software',
                'testimonials',
                'local SEO',
                'Google Business',
                'customer feedback',
            ];

            $results = $useApi
                ? $this->searchViaApi($subreddit->name, $keywords)
                : $this->publicScraper->fetchNewPosts($subreddit->name, $keywords);

            $seen = [];

            foreach ($results as $result) {
                if (isset($seen[$result['id']])) {
                    continue;
                }
                $seen[$result['id']] = true;

                if ($this->threadRepo->findByRedditId($result['id'])) {
                    continue;
                }

                if ($result['score'] < 1) {
                    continue;
                }

                $hoursSince = (time() - $result['created_utc']) / 3600;
                if ($hoursSince > 72) {
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
     * @return list<array<string, mixed>>
     */
    private function searchViaApi(string $subredditName, array $keywords): array
    {
        $results = [];

        foreach ($this->batchKeywords($keywords) as $query) {
            $results = array_merge($results, $this->redditApi->searchSubreddit($subredditName, $query));
            usleep(500_000);
        }

        return $results;
    }

    /**
     * @param  string[]  $keywords
     * @return string[]
     */
    private function batchKeywords(array $keywords, int $batchSize = 5): array
    {
        return array_map(
            fn (array $batch) => implode(' OR ', $batch),
            array_chunk($keywords, $batchSize),
        );
    }
}
