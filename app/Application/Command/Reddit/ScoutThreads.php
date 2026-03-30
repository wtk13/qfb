<?php

namespace App\Application\Command\Reddit;

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
    ) {}

    public function execute(): int
    {
        $subreddits = $this->subredditRepo->findActive();
        $newCount = 0;

        foreach ($subreddits as $subreddit) {
            $keywords = $subreddit->keywordsJson ?? ['reviews', 'feedback', 'reputation'];

            foreach ($keywords as $keyword) {
                $results = $this->redditApi->searchSubreddit($subreddit->name, $keyword);

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
                        discoveredAt: new \DateTimeImmutable(),
                        createdAt: new \DateTimeImmutable(),
                    );

                    $thread = $this->threadRepo->save($thread);
                    $newCount++;
                }

                usleep(500_000);
            }

            sleep(1);
        }

        return $newCount;
    }
}
