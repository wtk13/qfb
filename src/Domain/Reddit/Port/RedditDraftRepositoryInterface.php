<?php

declare(strict_types=1);

namespace Domain\Reddit\Port;

use Domain\Reddit\Entity\RedditDraft;
use Domain\Reddit\ValueObject\DraftStatus;

interface RedditDraftRepositoryInterface
{
    public function findById(int $id): ?RedditDraft;

    /** @return RedditDraft[] */
    public function findByStatus(DraftStatus $status, int $limit = 50): array;

    public function save(RedditDraft $draft): RedditDraft;

    /**
     * @return array{value: int, discussion: int, brand: int}
     */
    public function countByContentCategoryBetween(\DateTimeImmutable $from, \DateTimeImmutable $to): array;

    /**
     * @return array{posts: int, comments: int}
     */
    public function countPublishedThisWeek(int $subredditId): array;

    public function purgeOlderThan(\DateTimeImmutable $date, array $statuses): int;
}
