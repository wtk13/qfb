<?php

declare(strict_types=1);

namespace Domain\Reddit\Entity;

final class RedditStrategyReport
{
    public function __construct(
        public readonly int $id,
        public readonly \DateTimeImmutable $periodStart,
        public readonly \DateTimeImmutable $periodEnd,
        public readonly array $reportJson,
        public readonly array $recommendationsJson,
        public readonly array $contentRatioJson,
        public readonly array $topPerformingJson,
        public readonly \DateTimeImmutable $createdAt,
    ) {}
}
