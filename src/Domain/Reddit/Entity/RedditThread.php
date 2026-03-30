<?php

declare(strict_types=1);

namespace Domain\Reddit\Entity;

use Domain\Reddit\ValueObject\ThreadStatus;
use Domain\Reddit\ValueObject\ThreadType;

final class RedditThread
{
    public function __construct(
        public readonly int $id,
        public readonly int $subredditId,
        public readonly string $redditId,
        public readonly string $title,
        public readonly ?string $body,
        public readonly string $author,
        public readonly string $url,
        public readonly int $score,
        public readonly int $numComments,
        public readonly ThreadType $threadType,
        public ThreadStatus $status,
        public readonly \DateTimeImmutable $discoveredAt,
        public readonly \DateTimeImmutable $createdAt,
    ) {}

    public function markStale(): void
    {
        $this->status = ThreadStatus::Stale;
    }

    public function markDrafted(): void
    {
        $this->status = ThreadStatus::Drafted;
    }

    public function isStale(\DateTimeImmutable $now = new \DateTimeImmutable()): bool
    {
        $hoursSinceDiscovery = ($now->getTimestamp() - $this->discoveredAt->getTimestamp()) / 3600;
        return $hoursSinceDiscovery > 24;
    }
}
