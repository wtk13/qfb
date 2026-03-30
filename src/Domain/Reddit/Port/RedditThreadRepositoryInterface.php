<?php

declare(strict_types=1);

namespace Domain\Reddit\Port;

use Domain\Reddit\Entity\RedditThread;

interface RedditThreadRepositoryInterface
{
    public function findById(int $id): ?RedditThread;

    public function findByRedditId(string $redditId): ?RedditThread;

    /** @return RedditThread[] */
    public function findNewThreads(int $limit = 10): array;

    public function save(RedditThread $thread): RedditThread;

    public function markStaleThreads(\DateTimeImmutable $olderThan): int;

    public function purgeOlderThan(\DateTimeImmutable $date, array $statuses): int;
}
